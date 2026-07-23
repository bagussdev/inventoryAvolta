<?php

namespace App\Http\Controllers;

use App\Models\Desktop;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DesktopsExport;


class DesktopController extends Controller
{
    use AuthorizesRequests;

    protected function buildIndexQuery(Request $request)
    {
        $search = $request->input('search');
        $prefix = 'WSDPPADPS';

        return Desktop::query()
            ->withoutTrashed()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('hostname', 'like', "%$search%")
                        ->orWhere('serialnumber', 'like', "%$search%")
                        ->orWhere('brand', 'like', "%$search%")
                        ->orWhere('model', 'like', "%$search%")
                        ->orWhere('user', 'like', "%$search%")
                        ->orWhere('iprealvnc', 'like', "%$search%");
                })
                    ->orWhereHas('store', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%$search%");
                    });
            })
            ->orderByRaw("CAST(SUBSTRING(hostname, LENGTH(?) + 2) AS UNSIGNED) DESC", [$prefix]);
    }


    /** Index */
    public function index(Request $request)
    {
        $this->authorize('desktopsmenu');

        $search       = $request->input('search');
        $perPageInput = $request->input('per_page', 5);

        $forceAll = $perPageInput === 'all' || ($search && !$request->has('per_page'));

        $query = $this->buildIndexQuery($request);

        if ($forceAll) {
            $total    = (clone $query)->count();
            $desktops = $query->paginate(max($total, 1))->appends($request->query());
            $perPage  = 'all';
        } else {
            $perPage  = is_numeric($perPageInput) ? (int) $perPageInput : 5;
            $desktops = $query->paginate($perPage)->appends($request->query());
        }

        $rawLatest = Desktop::withoutTrashed()
            ->select(DB::raw('GREATEST(MAX(updated_at), MAX(created_at)) as ts'))
            ->value('ts');
        $latestTs  = $rawLatest ? Carbon::parse($rawLatest)->toIso8601String() : now()->toIso8601String();

        $baseOffset = ($desktops instanceof \Illuminate\Pagination\LengthAwarePaginator)
            ? (($desktops->currentPage() - 1) * $desktops->perPage())
            : 0;

        // Ambil angka terbesar dari hostname (termasuk soft delete)
        $lastNumber = Desktop::withTrashed()
            ->selectRaw("MAX(CAST(SUBSTRING(hostname, LENGTH('WSDPPADPS') + 2) AS UNSIGNED)) as max_number")
            ->value('max_number') ?? 0;

        $prevHostname = $lastNumber > 0
            ? "WSDPPADPS-" . str_pad($lastNumber, 4, '0', STR_PAD_LEFT)
            : null;

        $nextHostname = "WSDPPADPS-" . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return view('desktops.index', compact(
            'desktops',
            'search',
            'perPage',
            'latestTs',
            'baseOffset',
            'prevHostname',
            'nextHostname'
        ));
    }

    public function syncChanges(Request $request)
    {
        $this->authorize('desktopsmenu');

        // Parsing since
        $sinceIso = (string) $request->query('since', '');
        try {
            $since = $sinceIso ? Carbon::parse($sinceIso) : Carbon::now()->subYears(10);
        } catch (\Exception $e) {
            $since = Carbon::now()->subYears(10);
        }
        $since2 = (clone $since)->subSeconds(2);

        $base = $this->buildIndexQuery($request);

        // Pagination awareness
        $perPageArg = $request->query('per_page');
        $perPage = ($perPageArg !== null && $perPageArg !== 'all') ? (int) $perPageArg : null;
        $page = max((int) $request->query('page', 1), 1);

        $expectedIds = null;
        if ($perPage) {
            $expectedIds = (clone $base)
                ->select('id')
                ->forPage($page, $perPage)
                ->pluck('id')
                ->all();
        }

        // Cari created & updated
        $created = (clone $base)
            ->reorder() // buang order by
            ->where('created_at', '>=', $since2)
            ->pluck('id')
            ->all();

        $updated = (clone $base)
            ->reorder()
            ->where('updated_at', '>=', $since2)
            ->where('created_at', '<', $since2)
            ->pluck('id')
            ->all();

        // 🔥 Filter supaya hanya yang masuk ke halaman ini
        if ($expectedIds !== null) {
            $created = array_values(array_intersect($created, $expectedIds));
            $updated = array_values(array_intersect($updated, $expectedIds));
        } else {
            // fallback: tetap limit ke data visible supaya ga ALL
            $visible = (array) $request->query('visible');
            $created = array_values(array_intersect($created, $visible));
            $updated = array_values(array_intersect($updated, $visible));
        }

        // Cari deleted
        $visible = array_values(array_filter((array) $request->query('visible'), fn($v) => is_numeric($v)));
        $deleted = [];
        if (!empty($visible)) {
            if ($expectedIds !== null) {
                $deleted = array_values(array_diff($visible, $expectedIds));
            } else {
                $existingVisible = (clone $base)
                    ->whereIn('id', $visible)
                    ->pluck('id')
                    ->all();
                $deleted = array_values(array_diff($visible, $existingVisible));
            }
        }

        // Cari latest_ts aman (reset order & limit)
        $query = (clone $base);
        $query->getQuery()->orders = null;
        $query->getQuery()->limit = null;

        $rawLatest = $query
            ->selectRaw('MAX(updated_at) as max_updated, MAX(created_at) as max_created')
            ->first();

        if ($rawLatest && ($rawLatest->max_updated || $rawLatest->max_created)) {
            $latest = Carbon::parse(
                max($rawLatest->max_updated, $rawLatest->max_created)
            )->toIso8601String();
        } else {
            $latest = Carbon::now()->toIso8601String();
        }

        return response()->json([
            'latest_ts' => $latest,
            'created'   => $created,
            'updated'   => $updated,
            'deleted'   => $deleted,
        ]);
    }

    /** === Polling: render partial rows === */
    public function rows(Request $request)
    {
        $this->authorize('desktopsmenu');

        $ids = array_filter((array) $request->query('ids'), fn($v) => is_numeric($v));
        if (!$ids) return response('');

        $desktops = Desktop::whereIn('id', $ids)
            ->orderByDesc('id')
            ->get();

        return view('desktops._rows', compact('desktops'));
    }

    /** CRUD */
    public function create()
    {
        $this->authorize('desktops.create');
        $stores = Store::orderBy('name')->get();
        $nextHostname = Desktop::generateNextHostname();
        return view('desktops.create', compact('stores', 'nextHostname'));
    }

    public function store(Request $request)
    {
        $this->authorize('desktops.create');

        $validated = $request->validate([
            'serialnumber' => 'nullable|string|max:255|unique:desktops,serialnumber',
            'model'        => 'nullable|string|max:255',
            'brand'        => 'nullable|string|max:255',
            'location'     => 'required|exists:store,id',
            'typewindows'  => 'nullable|string|max:255',
            'user'         => 'nullable|string|max:255',
            'iprealvnc'    => 'nullable|string|max:255|unique:desktops,iprealvnc',
            'osstatus'     => 'nullable|string|max:255',
        ]);

        if ((int)$validated['location'] === 11) {
            $validated['status'] = 'scrap';
        } elseif ((int)$validated['location'] === 6) {
            $validated['status'] = 'broken';
        } elseif (!empty($validated['user'])) {
            $validated['status'] = 'in_use';
        } else {
            $validated['status'] = 'available';
        }

        $validated['created_by'] = Auth::id();

        Desktop::create($validated);

        return redirect()
            ->route('desktops.index')
            ->with('success', 'Desktop berhasil ditambahkan.');
    }

    public function show(Desktop $desktop)
    {
        $this->authorize('desktopsmenu');
        return view('desktops.show', compact('desktop'));
    }

    public function edit(Desktop $desktop)
    {
        $this->authorize('desktops.edit');
        $stores = Store::orderBy('name')->get();
        return view('desktops.edit', compact('desktop', 'stores'));
    }

    public function update(Request $request, Desktop $desktop)
    {
        $this->authorize('desktops.edit');

        $validated = $request->validate([
            'serialnumber' => 'required|string|max:255|unique:desktops,serialnumber,' . $desktop->id,
            'model'        => 'required|string|max:255',
            'brand'        => 'required|string|max:255',
            'location'     => 'required|exists:store,id',
            'typewindows'  => 'nullable|string|max:255',
            'user'         => 'nullable|string|max:255',
            'iprealvnc'    => 'nullable|string|max:255|unique:desktops,iprealvnc,' . $desktop->id,
            'osstatus'     => 'nullable|string|max:255',
        ]);

        if ($validated['location'] == 11) {
            $validated['status'] = 'scrap';
        } elseif ($validated['location'] == 6) {
            $validated['status'] = 'broken';
        } elseif (!empty($validated['user'])) {
            $validated['status'] = 'in_use';
        } else {
            $validated['status'] = 'available';
        }

        $validated['created_by'] = $desktop->created_by ?? Auth::id();

        $desktop->update($validated);

        return redirect()
            ->route('desktops.index')
            ->with('success', 'Desktop berhasil diperbarui.');
    }

    public function destroy(Desktop $desktop)
    {
        $this->authorize('desktops.delete');
        $desktop->delete();
        return redirect()->route('desktops.index')->with('success', 'Desktop berhasil dihapus.');
    }

    /** Template Import/Export */
    public function downloadTemplate()
    {
        $path = public_path('templates/desktops_template.xlsx');
        if (!file_exists($path)) {
            abort(404, 'Template not found.');
        }
        return response()->download($path);
    }


    public function export()
    {
        $this->authorize('desktopsmenu');
        return Excel::download(new DesktopsExport, 'desktops.xlsx');
    }

    public function importSave(Request $request)
    {
        $this->authorize('desktops.create');

        $data = json_decode($request->json_data, true);
        $errors = [];

        if (!is_array($data) || count($data) < 2) {
            return back()->with('error', 'Format file tidak valid atau data kosong.');
        }

        $expectedHeader = [
            'Hostname',
            'SerialNumber',
            'Model',
            'Brand',
            'User',
            'Location',
            'TypeWindows',
            'OSStatus',
            'IPRealVNC'
        ];
        $actualHeader = $data[0];

        if ($actualHeader !== $expectedHeader) {
            return back()->with('error', 'Header Excel tidak sesuai. Harus: ' . implode(', ', $expectedHeader));
        }

        $inserted = 0;
        $userId = Auth::id();

        $existingHostnames = array_map('strtoupper', Desktop::pluck('hostname')->toArray());
        $existingSNs       = array_map('strtoupper', Desktop::pluck('serialnumber')->toArray());
        $existingIPs       = array_map('strtoupper', Desktop::pluck('iprealvnc')->toArray());

        $seenHostnames = [];
        $seenSNs       = [];
        $seenIPs       = [];

        foreach (array_slice($data, 1) as $index => $row) {
            $rowNumber = $index + 2;
            $row = array_pad($row, count($expectedHeader), null);

            // ✅ skip baris kosong
            if (count(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== '')) === 0) {
                continue;
            }

            [$hostname, $serial, $model, $brand, $userField, $location, $typewindows, $osstatus, $ip] = $row;

            $hostname    = strtoupper(trim((string) $hostname));
            $serial      = $serial ? strtoupper(trim((string) $serial)) : null;
            $model       = $model ? strtoupper(trim((string) $model)) : null;
            $brand       = $brand ? strtoupper(trim((string) $brand)) : null;
            $typewindows = $typewindows ? strtoupper(trim((string) $typewindows)) : null;
            $osstatus    = $osstatus ? strtoupper(trim((string) $osstatus)) : null;
            $ip          = $ip ? trim((string) $ip) : null;

            // === Validasi ===
            if (empty($hostname)) {
                $errors[] = "Baris $rowNumber: Hostname wajib diisi.";
                continue;
            }

            if (!preg_match('/^WSDPPADPS-\d+$/', $hostname)) {
                $errors[] = "Baris $rowNumber: Hostname $hostname tidak valid. Harus diawali 'WSDPPADPS-'.";
                continue;
            }

            if (in_array($hostname, $seenHostnames)) {
                $errors[] = "Baris $rowNumber: Hostname $hostname duplikat dalam file.";
                continue;
            }
            if ($serial && in_array($serial, $seenSNs)) {
                $errors[] = "Baris $rowNumber: SerialNumber $serial duplikat dalam file.";
                continue;
            }
            if ($ip && in_array($ip, $seenIPs)) {
                $errors[] = "Baris $rowNumber: IPRealVNC $ip duplikat dalam file.";
                continue;
            }

            if (in_array($hostname, $existingHostnames)) {
                $errors[] = "Baris $rowNumber: Hostname $hostname sudah terdaftar.";
                continue;
            }
            if ($serial && in_array($serial, $existingSNs)) {
                $errors[] = "Baris $rowNumber: SerialNumber $serial sudah terdaftar.";
                continue;
            }
            if ($ip && in_array($ip, $existingIPs)) {
                $errors[] = "Baris $rowNumber: IPRealVNC $ip sudah terdaftar.";
                continue;
            }

            $seenHostnames[] = $hostname;
            if ($serial) $seenSNs[] = $serial;
            if ($ip) $seenIPs[] = $ip;

            // Cari store
            $store = Store::where('name', $location)->first();
            if (!$store) {
                $errors[] = "Baris $rowNumber: Store '$location' tidak ditemukan.";
                continue;
            }
            $locationId = $store->id;

            $status = $userField ? 'in_use' : 'available';

            Desktop::create([
                'hostname'     => $hostname,
                'serialnumber' => $serial,
                'model'        => $model,
                'brand'        => $brand,
                'user'         => $userField ?: null,
                'location'     => $locationId,
                'typewindows'  => $typewindows,
                'osstatus'     => $osstatus,
                'iprealvnc'    => $ip,
                'status'       => $status,
                'created_by'   => $userId,
            ]);

            $inserted++;
        }

        if ($inserted > 0 && count($errors) === 0) {
            return redirect()->route('desktops.index')->with('success', "$inserted desktop berhasil diimport.");
        } elseif ($inserted > 0 && count($errors) > 0) {
            return redirect()->route('desktops.index')->with([
                'success' => "$inserted desktop berhasil diimport.",
                'error'   => implode("\n", $errors), // pakai newline
            ]);
        } else {
            return back()->with('error', 'Gagal mengimport' . "\n" . implode("\n", $errors));
        }
    }
}
