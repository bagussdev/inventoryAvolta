<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class OutletDataController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('outletlistmenu');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 5);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $storesQuery = Store::query()
            ->when(!$isMaster, function ($query) {
                $query->where('type', 'store');
            })
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('site_code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });

        if ($perPage === 'all') {
            $stores = $storesQuery->orderByDesc('updated_at')->get();
        } else {
            $stores = $storesQuery->orderByDesc('updated_at')
                ->paginate((int) $perPage)
                ->appends([
                    'search' => $search,
                    'per_page' => $perPage,
                ]);
        }

        return view('outletList.index', compact('stores', 'search', 'perPage'));
    }

    public function tbody(Request $request)
    {
        $this->authorize('outletlistmenu');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 5);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $storesQuery = Store::query()
            ->when(!$isMaster, function ($query) {
                $query->where('type', 'store');
            })
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('site_code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });

        if ($perPage === 'all') {
            $stores = $storesQuery->orderByDesc('updated_at')->get();
        } else {
            $stores = $storesQuery->orderByDesc('updated_at')
                ->paginate((int) $perPage)
                ->appends([
                    'search' => $search,
                    'per_page' => $perPage,
                ]);
        }

        return view('partials.outlets-tbody', compact('stores'));
    }

    public function lastUpdated()
    {
        $this->authorize('outletlistmenu');

        $lastUpdated = Store::max('updated_at');
        return response()->json(['last_updated' => $lastUpdated]);
    }

    public function create()
    {
        $this->authorize('outletlist.create');
        return view('outletList.create');
    }
    public function store(Request $request)
    {
        $this->authorize('outletlist.create');
        $request->validate([
            'name' => 'required|string|max:255',
            'site_code' => 'required|string|max:255|unique:store,site_code',
            'since' => 'required|date',
            'location' => 'required|string|max:255',
            'status' => 'required|in:Y,N',
        ]);

        Store::create([
            'name' => $request->name,
            'site_code' => $request->site_code,
            'since' => $request->since,
            'location' => $request->location,
            'status' => $request->status,
            'type' => 'Store',
        ]);

        return redirect()->route('outlets.index')->with('success', 'Outlet berhasil ditambahkan.');
    }
    public function active($id)
    {
        $this->authorize('outletcontrol');
        $store = Store::findOrFail($id);
        $store->update(['status' => 'Y']);

        return redirect()->route('outlets.index')->with('success', 'Outlet diaktifkan.');
    }

    public function deactive($id)
    {
        $this->authorize('outletcontrol');
        $store = Store::findOrFail($id);
        $store->update(['status' => 'N']);

        return redirect()->route('outlets.index')->with('success', 'Outlet dinonaktifkan.');
    }
    public function edit($id)
    {
        $this->authorize('outletlist.edit');
        $store = Store::findOrFail($id);
        return view('outletList.edit', compact('store'));
    }
    public function update(Request $request, $id)
    {
        $this->authorize('outletlist.edit');
        $request->validate([
            'name' => 'required|string|max:255',
            'site_code' => 'required|string|max:255|unique:store,site_code,' . $id . ',id',
            'since' => 'required|date',
            'location' => 'required|string|max:255',
        ]);

        $store = Store::findOrFail($id);
        $store->update($request->all());

        return redirect()->route('outlets.index')->with('success', 'Outlet berhasil diperbarui.');
    }

    public function importSave(Request $request)
    {
        $data = json_decode($request->json_data, true);

        // Skip header
        $rows = collect($data)->skip(1);

        if ($rows->isEmpty()) {
            return redirect()->route('outlets.index')->with('error', 'Data Excel kosong atau tidak valid.');
        }

        $errors = [];

        foreach ($rows as $index => $row) {
            // Validasi kolom wajib
            if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                $errors[] = "Baris ke-" . ($index + 2) . " tidak lengkap.";
                continue;
            }

            // Validasi dan parsing tanggal
            try {
                $parsedSince = Carbon::parse($row[2]);
            } catch (\Exception $e) {
                $errors[] = "Baris ke-" . ($index + 2) . " memiliki format tanggal tidak valid.";
                continue;
            }

            Store::create([
                'name' => $row[0],
                'site_code' => $row[1],
                'since' => $parsedSince,
                'location' => $row[3] ?? null,
                'status' => 'Y', // default aktif
            ]);
        }

        if (count($errors) > 0) {
            return redirect()->route('outletList.index')->with('error', implode(' ', $errors));
        }

        return redirect()->route('outletList.index')->with('success', 'Outlet berhasil diimpor!');
    }

    public function downloadTemplate()
    {
        $filePath = public_path('templates/template_outlet.xlsx');
        return response()->download($filePath, 'template_outlet.xlsx');
    }

    public function show($id)
    {
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $outlet = Store::with(['users'])->findOrFail($id);

        // ambil equipments dengan item yang sesuai department
        $equipments = $outlet->equipments()
            ->with('item')
            ->when(!$isMaster, function ($query) use ($user) {
                $query->whereHas('item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            })->get();

        // inject manual ke relasi agar tetap bisa pakai $outlet->equipments di blade
        $outlet->setRelation('equipments', $equipments);

        return view('outletList.show', compact('outlet'));
    }

}
