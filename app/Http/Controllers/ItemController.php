<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Department;
use App\Models\Transaction;
use App\Models\NotificationPreference;
use App\Services\NotificationService;

class ItemController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        // 1. Otorisasi
        $this->authorize('inventoryitemsmenu');

        // 2. Ambil parameter dari request
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');

        if ($search && !$perPage) {
            $perPage = 'all';
        }
        // 3. Dapatkan user yang sedang login dan cek role-nya
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // 4. Mulai query
        $itemsQuery = Item::query()
            ->with('department') // Eager load relasi department

            // 5. Terapkan filter pencarian dari input user
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            });

        // 6. Terapkan filter departemen jika user BUKAN Master
        if (!$isMaster) {
            if ($user->department_id) {
                $itemsQuery->where('department_id', $user->department_id);
            } else {
                $itemsQuery->whereNull('department_id');
            }
        }

        // 7. Urutkan berdasarkan yang terbaru (tanggal dibuat)
        // Ini adalah cara paling efisien untuk mengurutkan terbaru
        $isFiltered = $search;
        if ($perPage === 'all' || $isFiltered) {
            $items = $itemsQuery->orderBy('created_at', 'desc')->get();
        } else {
            $perPage = (int) ($perPage ?: 5);
            $items = $itemsQuery->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends($request->query());
        }
        // 8. Kirim data ke view
        return view('inventoryitems.index', compact('items', 'search', 'perPage'));
    }
    public function tbody(Request $request)
    {
        $this->authorize('inventoryitemsmenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $itemsQuery = Item::query()
            ->with('department')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            });

        if (!$isMaster) {
            if ($user->department_id) {
                $itemsQuery->where('department_id', $user->department_id);
            } else {
                $itemsQuery->whereNull('department_id');
            }
        }

        $isFiltered = $search;
        if ($perPage === 'all' || $isFiltered) {
            $items = $itemsQuery->orderBy('created_at', 'desc')->get();
        } else {
            $perPage = (int) ($perPage ?: 5);
            $items = $itemsQuery->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends($request->query());
        }

        return view('partials.items-tbody', compact('items', 'search', 'perPage'));
    }
    public function lastUpdated()
    {
        $lastUpdated = Item::max('updated_at');
        return response()->json(['last_updated' => $lastUpdated]);
    }
    public function show($id)
    {
        $item = Item::with(['sparepart.usedSpareparts.maintenance', 'sparepart.usedSpareparts.incident', 'sparepart.usedSpareparts.request'])->findOrFail($id);

        $history = collect();

        // Transaksi masuk (in)
        $transactions = Transaction::where('items_id', $item->id)->get();
        foreach ($transactions as $trx) {
            $history->push([
                'date' => $trx->created_at->format('Y-m-d'),
                'type' => 'in',
                'qty' => $trx->qty,
                'note' => $trx->notes ?? '-',
                'reference' => 'TRX' . str_pad($trx->id, 5, '0', STR_PAD_LEFT),
                'ref_type' => 'transaction',
                'ref_id' => $trx->id,
            ]);
        }

        // Transaksi keluar (out) dari used_spareparts
        foreach ($item->sparepart as $sparepart) {
            foreach ($sparepart->usedSpareparts as $used) {
                $refType = null;
                $refId = null;
                $reference = '-';

                if ($used->maintenance_id) {
                    $refType = 'maintenance';
                    $refId = $used->maintenance_id;
                    $reference = 'MNT' . str_pad($refId, 5, '0', STR_PAD_LEFT);
                } elseif ($used->incident_id) {
                    $refType = 'incident';
                    $refId = $used->incident_id;
                    $reference = $used->incident?->unique_id ?? 'INC-' . str_pad($refId, 4, '0', STR_PAD_LEFT);
                } elseif ($used->request_id) {
                    $refType = 'request';
                    $refId = $used->request_id;
                    $reference = $used->request?->unique_id ?? 'REQ-' . str_pad($refId, 4, '0', STR_PAD_LEFT);
                }

                $history->push([
                    'date' => $used->created_at->format('Y-m-d'),
                    'type' => 'out',
                    'qty' => $used->qty,
                    'note' => $used->note ?? '-',
                    'reference' => $reference,
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                ]);
            }
        }

        // Urutkan dari tanggal terbaru
        $history = $history->sortByDesc('date')->values();
        // Hitung total masuk dan keluar
        $totalIn = $history->where('type', 'in')->sum('qty');
        $totalOut = $history->where('type', 'out')->sum('qty');
        $totalStock = $totalIn - $totalOut;

        return view('inventoryitems.show', compact('item', 'history', 'totalIn', 'totalOut', 'totalStock'));
    }
    public function create()
    {
        $this->authorize('inventoryitems.create');
        $departments = Gate::allows('isMaster') ? Department::all() : collect();
        return view('inventoryitems.create', compact('departments'));
    }

    private function getNotificationTargets(string $type, int $departmentId = null): array
    {
        $preferences = NotificationPreference::where('type', $type)->pluck('role_id')->toArray();

        $targets = [];

        foreach ($preferences as $roleId) {
            if (in_array($roleId, [1])) { // Master tanpa department
                $targets[] = ['role_id' => $roleId];
            } elseif ($departmentId) {
                $targets[] = ['role_id' => $roleId, 'department_id' => $departmentId];
            }
        }

        return $targets;
    }


    public function store(Request $request)
    {
        $this->authorize('inventoryitems.create');

        $isMaster = Gate::allows('isMaster');
        $user = Auth::user();
        // 1. Tentukan aturan validasi
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:Unit,Pcs,Box',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'category' => 'required|in:equipment,sparepart',
        ];

        if ($isMaster) {
            $rules['department_id'] = 'required|exists:departments,id';
        }

        // 2. Lakukan validasi
        $validatedData = $request->validate($rules);

        // 3. Cek duplikat
        $existingItem = Item::where('name', $validatedData['name'])
            ->where('brand', $validatedData['brand'])
            ->where('model', $validatedData['model'])
            ->first();

        if ($existingItem) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Item is already available.');
        }

        // 4. Tentukan department_id
        if (!$isMaster) {
            // Jika bukan Master, ambil department_id dari user yang login

            if (!$user->department_id) {
                return redirect()->back()->with('error', 'Akun Anda tidak terhubung dengan departemen manapun.')->withInput();
            }
            $validatedData['department_id'] = $user->department_id;
        }
        // Jika user adalah Master, department_id sudah ada di $validatedData dari request

        // 5. Simpan item ke database
        $validatedData['name'] = strtoupper($validatedData['name']);
        $validatedData['brand'] = strtoupper($validatedData['brand']);
        $validatedData['model'] = strtoupper($validatedData['model']);


        $item = Item::create($validatedData);

        $targets = $this->getNotificationTargets('create_item', $item->department_id);

        NotificationService::send(
            $targets,
            'item',
            'New Item Added',
            'Item "' . $item->name . '" has been added to department by ' . $user->name . '.',
            'items',
            $item->id
        );
        return redirect()->route('items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $this->authorize('inventoryitems.edit');
        return view('inventoryitems.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $this->authorize('inventoryitems.edit');
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:Unit,Pcs,Box',
            'brand' => 'nullable',
            'model' => 'nullable',
            'category' => 'nullable',
        ]);

        $item->update($request->all());

        // --- Tambah Notifikasi ---
        $user = Auth::user();
        $targets = $this->getNotificationTargets('edit_item', $item->department_id);

        NotificationService::send(
            $targets,
            'item',
            'Item Updated',
            'Item "' . $item->name . '" was updated by ' . $user->name . '.',
            'items',
            $item->id
        );


        return redirect()->route('items.index')->with('success', 'Item berhasil diperbarui.');
    }


    public function destroy(Item $item)
    {
        $user = Auth::user();
        $departmentId = $item->department_id;

        $item->delete();

        $targets = $this->getNotificationTargets('delete_item', $departmentId);

        NotificationService::send(
            $targets,
            'item',
            'Item Deleted',
            'Item "' . $item->name . '" has been deleted by ' . $user->name . '.',
            'items',
            $item->id
        );

        return redirect()->route('items.index')->with('success', 'Item berhasil dihapus.');
    }

    public function ryclebin(Request $request)
    {
        $this->authorize('inventoryitemsmenu');
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');

        $items = Item::onlyTrashed()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            })
            ->paginate($perPage)
            ->appends([
                'search' => $search,
                'per_page' => $perPage,
            ]);

        return view('inventoryitems.deleted', compact('items', 'search', 'perPage'));
    }
    public function restore($id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $item->restore();

        $user = Auth::user();

        $targets = $this->getNotificationTargets('restore_item', $item->department_id);

        NotificationService::send(
            $targets,
            'item',
            'Item Restored',
            'Item "' . $item->name . '" has been restored by ' . $user->name . '.',
            'items',
            $item->id
        );


        return redirect()->route('items.deleted')->with('success', 'Item berhasil dikembalikan.');
    }
    public function permanentDeleteAll()
    {
        $this->authorize('inventoryitemsmenu');

        $deletedItems = Item::onlyTrashed()->get();

        if ($deletedItems->isEmpty()) {
            return redirect()->route('items.deleted')->with('error', 'Nothing to delete.');
        }

        foreach ($deletedItems as $item) {
            $item->forceDelete();
        }

        return redirect()->route('items.deleted')->with('success', 'Semua item berhasil dihapus permanen.');
    }
    public function importSave(Request $request)
    {
        $this->authorize('inventoryitems.create');

        $data = json_decode($request->json_data, true);
        $errors = [];

        // Validasi minimal ada header + 1 baris data
        if (!is_array($data) || count($data) < 2) {
            return back()->with('error', 'Format file tidak valid atau data kosong.');
        }

        // Cek header (opsional, jika kamu ingin pastikan header sesuai)
        $expectedHeader = ['Name', 'Type', 'Brand', 'Model', 'Category'];
        $actualHeader = $data[0];
        if ($actualHeader !== $expectedHeader) {
            return back()->with('error', 'Header Excel tidak sesuai. Harus: Name, Type, Brand, Model, Category');
        }

        $inserted = 0;
        $user = Auth::user();
        if (!$user->department_id) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan departemen manapun. Tidak dapat melakukan import.');
        }
        $departmentId = $user->department_id;

        foreach (array_slice($data, 1) as $index => $row) {
            $rowNumber = $index + 2;

            // Validasi jumlah kolom
            if (count($row) < 5) {
                $errors[] = "Baris $rowNumber: Kolom tidak lengkap.";
                continue;
            }

            [$name, $type, $brand, $model, $category] = $row;

            // Validasi type dan category
            if (!in_array($type, ['Unit', 'Pcs', 'Box'])) {
                $errors[] = "Baris $rowNumber: Type tidak valid ($type).";
                continue;
            }

            if (!in_array($category, ['equipment', 'sparepart'])) {
                $errors[] = "Baris $rowNumber: Category tidak valid ($category).";
                continue;
            }

            // Cek duplikat
            $exists = Item::where('name', $name)
                ->where('brand', $brand)
                ->where('model', $model)
                ->first();

            if ($exists) {
                $errors[] = "Baris $rowNumber: Item sudah ada ($name - $brand - $model).";
                continue;
            }

            // Simpan
            $item = Item::create([
                'name' => strtoupper($name),
                'type' => $type,
                'brand' => strtoupper($brand),
                'model' => strtoupper($model),
                'category' => $category,
                'department_id' => $departmentId
            ]);

            $inserted++;

            $targets = $this->getNotificationTargets('import_item', $item->department_id);
        }


        if ($inserted > 0 && count($errors) === 0) {
            NotificationService::send(
                $targets,
                'item',
                'Item Imported',
                'Item "' . $item->name . '" has been imported by ' . $user->name . '.',
                'items',
                $item->id
            );
            return redirect()->route('items.index')->with('success', "$inserted item berhasil diimport.");
        } elseif ($inserted > 0 && count($errors) > 0) {
            return redirect()->route('items.index')->with([
                'success' => "$inserted item berhasil diimport.",
                'error' => implode('<br>', $errors),
            ]);
        } else {
            return back()->with('error', 'Gagal mengimport. <br>' . implode('<br>', $errors));
        }
    }

    public function downloadTemplate()
    {
        return response()->download(public_path('templates/items_template.xlsx'));
    }
}
