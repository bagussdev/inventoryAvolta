<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Store;
use App\Models\Sparepart;
use App\Models\UsedSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaintenancesExport;
use App\Exports\MaintenancesCompletedExport;

class MaintenanceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // Otorisasi: Pastikan pengguna memiliki izin untuk melihat daftar maintenance
        // Ganti 'maintenance.view' dengan nama permission yang sesuai jika berbeda
        $this->authorize('maintenancemenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user(); // Ambil data user yang sedang login
        $isMaster = Gate::allows('isMaster'); // Cek apakah user adalah master

        $maintenancesQuery = Maintenance::query()
            // Eager load relasi yang dibutuhkan untuk tampilan tabel dan pencarian
            ->with(['equipment.item', 'equipment.store', 'staff', 'confirm'])
            ->whereNotIn('status', ['completed', 'cancelled']); // Filter status yang sudah ada

        // 1. Filter Pencarian (sesuai kode Anda)
        $maintenancesQuery->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) { // Group orWhere clauses correctly
                $q->where('id', 'like', "%{$search}%") // Maintenance ID
                    ->orWhere('frequensi', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('maintenance_date', 'like', "%{$search}%") // Pencarian tanggal dalam string
                    ->orWhereHas('equipment.item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment.store', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Asumsi 'picstaff' di kode Anda merujuk ke relasi 'staff'
                    ->orWhereHas('staff', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Asumsi 'confirmby' di kode Anda merujuk ke relasi 'confirm'
                    ->orWhereHas('confirm', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        });

        // --- TAMBAHAN BARU: LOGIKA GATE ISMASTER DI SINI ---
        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $maintenancesQuery->whereHas('equipment.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                // Jika user tidak punya department atau department_id-nya null, kembalikan query kosong
                $maintenancesQuery->whereNull('id');
            }
        }

        // --- TAMBAHAN BARU: Filter berdasarkan rentang tanggal `maintenance_date` ---
        $maintenancesQuery->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('maintenance_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        });

        // Urutkan berdasarkan maintenance_date terbaru
        $maintenances = $maintenancesQuery->orderBy('maintenance_date', 'desc')
            ->paginate($perPage);

        // appends() untuk mempertahankan semua filter pada pagination link
        $maintenances->appends(compact('search', 'perPage', 'startDate', 'endDate'));

        return view('maintenances.index', compact('maintenances', 'perPage', 'search', 'startDate', 'endDate'));
    }
    public function export(Request $request)
    {
        $this->authorize('maintenancemenu');

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user(); // Ambil data user yang sedang login
        $isMaster = Gate::allows('isMaster'); // Cek apakah user adalah master

        $maintenancesQuery = Maintenance::query()
            // Eager load relasi yang dibutuhkan untuk export
            ->with(['equipment.item', 'equipment.store', 'staff', 'confirm'])->whereNotIn('status', ['completed', 'cancelled']);;

        // 1. Filter Pencarian (sesuai kode di index)
        if ($search) {
            $maintenancesQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('frequensi', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('maintenance_date', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment.store', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('staff', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('confirm', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // --- TAMBAHAN BARU: LOGIKA GATE ISMASTER UNTUK EXPORT ---
        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $maintenancesQuery->whereHas('equipment.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $maintenancesQuery->whereNull('id');
            }
        }

        // 3. Filter Tanggal (sesuai kode di index)
        if ($startDate && $endDate) {
            $maintenancesQuery->whereBetween('maintenance_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $filteredMaintenances = $maintenancesQuery->orderBy('maintenance_date', 'desc')->get();

        $filename = 'maintenances_filtered_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new MaintenancesExport($filteredMaintenances), $filename);
    }
    public function completed(Request $request)
    {
        // Otorisasi
        $this->authorize('completedmaintenancemenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $maintenancesQuery = Maintenance::query()
            ->with(['equipment.item', 'equipment.store', 'staff', 'confirm'])
            ->whereIn('status', ['completed']); // Filter status = 'completed'

        // 1. Filter Pencarian
        $maintenancesQuery->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('frequensi', 'like', "%{$search}%")
                    ->orWhere('maintenance_date', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment.store', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('staff', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('confirm', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        });

        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $maintenancesQuery->whereHas('equipment.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $maintenancesQuery->whereNull('id');
            }
        }

        // 3. Filter berdasarkan rentang tanggal `updated_at` (Resolved At)
        $maintenancesQuery->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        });

        // Urutkan berdasarkan tanggal resolved terbaru
        $maintenances = $maintenancesQuery->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('maintenances.completed', compact('maintenances', 'perPage', 'search', 'startDate', 'endDate'));
    }

    public function exportCompleted(Request $request)
    {
        // Otorisasi
        $this->authorize('completedmaintenancemenu');

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $maintenancesQuery = Maintenance::query()
            ->with(['equipment.item', 'equipment.store'])
            ->where('status', 'completed'); // Filter status = 'completed'

        // 1. Filter Pencarian
        if ($search) {
            $maintenancesQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('frequensi', 'like', "%{$search}%")
                    ->orWhere('maintenance_date', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment.store', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $maintenancesQuery->whereHas('equipment.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $maintenancesQuery->whereNull('id');
            }
        }

        // 3. Filter Tanggal
        if ($startDate && $endDate) {
            $maintenancesQuery->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Ambil semua data (tanpa paginate)
        $filteredMaintenances = $maintenancesQuery->orderBy('updated_at', 'desc')->get();

        // Mengirimkan koleksi data ke class export
        $filename = 'completed_maintenances_filtered_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new MaintenancesCompletedExport($filteredMaintenances), $filename);
    }


    public function show(Maintenance $maintenance)
    {
        $this->authorize('maintenancemenu');
        // 1. Eager load all necessary relationships for the view.
        $maintenance->load(['equipment.item', 'staff', 'confirm', 'usedSpareParts.sparepart.item']);

        // 2. Get the logged-in user and check their access using a Gate.
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster'); // Check the 'isMaster' Gate

        // 3. Fetch spareparts data with filtering logic.
        // If the user is a master, fetch all spareparts.
        if ($isMaster) {
            $spareparts = Sparepart::with('item')->get();
        }
        // If not a master, filter spareparts by the user's department via the `item` relationship.
        else {
            // Assumes: Sparepart -> Item -> Department
            $spareparts = Sparepart::with('item')
                ->whereHas('item', function ($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                })
                ->get();
        }
        // dd($spareparts->keyBy('id'));
        // 4. Pass the maintenance and spareparts variables to the view.
        return view('maintenances.show', compact('maintenance', 'spareparts'));
    }
    public function edit($id)
    {
        $this->authorize('schedulemaintenance.edit');
        $maintenance = Maintenance::with('equipment.item')->findOrFail($id);
        if ($maintenance->status !== 'not due') {
            abort(404);
        }
        $stores = Store::all();


        return view('maintenances.edit', compact('maintenance', 'stores'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('schedulemaintenance.edit');
        $request->validate([
            'frequensi' => 'required|in:weekly,monthly',
            'maintenance_date' => 'required|date',
        ]);

        $maintenance = Maintenance::findOrFail($id);
        $maintenance->frequensi = $request->frequensi;
        $maintenance->maintenance_date = $request->maintenance_date;

        $daysDiff = \Carbon\Carbon::now()->diffInDays($maintenance->maintenance_date, false);

        // Update status berdasarkan selisih hari
        if ($daysDiff <= 1) {
            $maintenance->status = 'maintenance';
        } else {
            $maintenance->status = 'not due';
        }

        $maintenance->save();

        return redirect()->route('maintenances.index')->with('success', 'Maintenance updated successfully.');
    }
    public function proses($id)
    {
        $this->authorize('maintenance.proses');
        $maintenance = Maintenance::findOrFail($id);

        if ($maintenance->status !== 'maintenance') {
            // If the status is not 'in progress', abort with a 404 error and a custom message.
            abort(404);
        }
        $maintenance->status = 'in progress';
        $maintenance->picstaff = Auth::id(); // pastikan login user adalah staff
        $maintenance->save();

        return redirect()->route('maintenances.index')->with('success', 'Maintenance is now in progress.');
    }
    public function confirm($id)
    {
        $this->authorize('maintenance.confirm');
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Retrieve maintenance with complete relationships
        $maintenance = Maintenance::with([
            'equipment.item.department',
            'equipment.store',
            'staff',
            'confirm'
        ])->findOrFail($id);

        // --- Add this logic to restrict access based on status ---
        if ($maintenance->status !== 'in progress') {
            // If the status is not 'in progress', abort with a 404 error and a custom message.
            abort(404);
        }
        $sparepartsQuery = Sparepart::where('qty', '>', 0)->with('item');

        if (!$isMaster && $user->department_id) {
            $sparepartsQuery->whereHas('item', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $spareparts = $sparepartsQuery->get();

        return view('maintenances.confirm', compact('maintenance', 'spareparts'));
    }


    public function submitConfirm(Request $request, $id)
    {
        $this->authorize('maintenance.confirm');
        $request->validate([
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:10240',
            'spareparts' => 'nullable|array',
            'spareparts.*.id' => 'nullable|exists:spareparts,id',
            'spareparts.*.qty' => 'nullable|integer|min:1',
            'spareparts.*.note' => 'nullable|string|max:255',
        ]);

        $maintenance = Maintenance::findOrFail($id);
        if ($request->filled('spareparts')) {
            foreach ($request->spareparts as $spare) {
                $sparepart = Sparepart::find($spare['id']);
                if (!$sparepart) continue;

                $availableStock = $sparepart->qty ?? 0;

                if ($spare['qty'] > $availableStock) {
                    return back()->with('error', 'Insufficient stock for sparepart: ' . $sparepart->item->name)->withInput();
                }
            }
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment')->store('maintenance_attachments', 'public');
            $maintenance->attachment = $file;
        }

        $maintenance->notes = $request->notes;
        $maintenance->status = 'resolved';
        $maintenance->resolved_at = Carbon::now();
        $maintenance->save();

        if ($request->filled('spareparts')) {
            foreach ($request->spareparts as $spare) {
                $sparepart = Sparepart::find($spare['id']);
                if (!$sparepart) continue;

                UsedSparepart::create([
                    'spareparts_id' => $sparepart->id,
                    'maintenance_id' => $maintenance->id,
                    'qty' => $spare['qty'],
                    'note' => $spare['note'] ?? null,
                ]);

                // Kurangi stok & update status
                $sparepart->qty -= $spare['qty'];

                $sparepart->status = $sparepart->qty < 0 ? 'empty' : ($sparepart->qty > 5 ? 'low' : 'available');

                $sparepart->save();
            }
        }

        return redirect()->route('maintenances.index')->with('success', 'Maintenance confirmed successfully.');
    }
    public function updateSpareparts(Request $request, $id)
    {
        $this->authorize('completedmaintenancemenu');
        $request->validate([
            'spareparts' => 'nullable|array',
            'spareparts.*.id' => 'required|exists:spareparts,id',
            'spareparts.*.qty' => 'required|integer|min:1',
            'spareparts.*.note' => 'nullable|string|max:255',
            'spareparts.*.used_id' => 'nullable|exists:used_spareparts,id',
            'deleted_ids' => 'nullable|array',
            'deleted_ids.*' => 'exists:used_spareparts,id',
        ]);

        $maintenance = Maintenance::with('usedSpareParts')->findOrFail($id);
        $oldUsedById = $maintenance->usedSpareParts->keyBy('id');
        $newInputs = collect($request->spareparts ?? []);
        $deletedIds = collect($request->deleted_ids ?? []);

        DB::beginTransaction();
        try {
            // Handle deletions
            foreach ($deletedIds as $deletedId) {
                $used = UsedSparepart::find($deletedId);
                if ($used && $used->maintenance_id === $maintenance->id) {
                    $sparepart = Sparepart::findOrFail($used->spareparts_id);
                    $sparepart->qty += $used->qty;
                    $sparepart->status = $this->determineStatus($sparepart->qty);
                    $sparepart->save();
                    $used->delete();
                }
            }

            $groupedInputs = [];

            foreach ($newInputs as $input) {
                $id = $input['id'];
                $qty = $input['qty'];
                $note = $input['note'] ?? null;
                $usedId = $input['used_id'] ?? null;

                if (!isset($groupedInputs[$id])) {
                    $groupedInputs[$id] = [
                        'id' => $id,
                        'qty' => $qty,
                        'note' => $note,
                        'used_id' => $usedId
                    ];
                } else {
                    $groupedInputs[$id]['qty'] += $qty;
                    if ($note && !str_contains($groupedInputs[$id]['note'], $note)) {
                        $groupedInputs[$id]['note'] .= '; ' . $note;
                    }
                }
            }

            // Lanjutkan proses seperti biasa
            foreach ($groupedInputs as $input) {
                $newSparepartId = $input['id'];
                $newQty = $input['qty'];
                $note = $input['note'] === '' ? null : $input['note'];
                $usedId = $input['used_id'] ?? null;

                $sparepart = Sparepart::with('item')->findOrFail($newSparepartId);

                if ($usedId && $oldUsedById->has($usedId)) {
                    $used = $oldUsedById[$usedId];
                    $oldSparepartId = $used->spareparts_id;
                    $oldQty = $used->qty;

                    if ($oldSparepartId != $newSparepartId) {
                        $oldSparepart = Sparepart::findOrFail($oldSparepartId);
                        $oldSparepart->qty += $oldQty;
                        $oldSparepart->status = $this->determineStatus($oldSparepart->qty);
                        $oldSparepart->save();

                        if ($sparepart->qty < $newQty) {
                            throw new \Exception("Insufficient stock for sparepart {$sparepart->item->name}. Available: {$sparepart->qty}.");
                        }
                        $sparepart->qty -= $newQty;
                        $sparepart->status = $this->determineStatus($sparepart->qty);
                        $sparepart->save();

                        $used->spareparts_id = $newSparepartId;
                        $used->qty = $newQty;
                        $used->note = $note;
                        $used->save();
                    } else {
                        $deltaQty = $newQty - $oldQty;
                        if ($deltaQty > 0 && $sparepart->qty < $deltaQty) {
                            throw new \Exception("Insufficient stock for sparepart {$sparepart->item->name}. Available: {$sparepart->qty}.");
                        }
                        $sparepart->qty -= $deltaQty;
                        $sparepart->status = $this->determineStatus($sparepart->qty);
                        $sparepart->save();

                        $used->qty = $newQty;
                        $used->note = $note;
                        $used->save();
                    }
                } else {
                    // Tambahan pengecekan kalau incident sudah pernah punya UsedSparepart dengan spareparts_id ini
                    $existingUsed = $maintenance->usedSpareParts()->where('spareparts_id', $newSparepartId)->first();

                    if ($existingUsed) {
                        $deltaQty = $newQty;
                        if ($sparepart->qty < $deltaQty) {
                            throw new \Exception("Insufficient stock for sparepart {$sparepart->item->name}. Available: {$sparepart->qty}.");
                        }

                        $sparepart->qty -= $deltaQty;
                        $sparepart->status = $this->determineStatus($sparepart->qty);
                        $sparepart->save();

                        $existingUsed->qty += $newQty;
                        if ($note && !str_contains($existingUsed->note, $note)) {
                            $existingUsed->note = trim($existingUsed->note . '; ' . $note, '; ');
                        }
                        $existingUsed->save();
                    } else {
                        if ($sparepart->qty < $newQty) {
                            throw new \Exception("Insufficient stock for sparepart {$sparepart->item->name}. Available: {$sparepart->qty}.");
                        }

                        $sparepart->qty -= $newQty;
                        $sparepart->status = $this->determineStatus($sparepart->qty);
                        $sparepart->save();

                        UsedSparepart::create([
                            'spareparts_id' => $newSparepartId,
                            'maintenance_id' => $maintenance->id,
                            'qty' => $newQty,
                            'note' => $note,
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Spareparts updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    private function determineStatus($qty)
    {
        return $qty <= 0 ? 'empty' : ($qty < 5 ? 'low' : 'available');
    }


    public function closed($id)
    {
        $this->authorize('maintenance.closed');
        $maintenance = Maintenance::findOrFail($id);
        if ($maintenance->status !== 'resolved') {
            // If the status is not 'in progress', abort with a 404 error and a custom message.
            abort(404);
        }
        // Update maintenance lama
        $maintenance->status = 'completed'; // atau 'closed' jika itu status akhirnya
        $maintenance->confirmBy = Auth::id();
        $maintenance->save();

        // Hitung tanggal maintenance baru: hari ini + frekuensi
        $nextDate = match (strtolower($maintenance->frequensi)) {
            'weekly' => Carbon::now()->addWeek(),
            'monthly' => Carbon::now()->addMonth(),
            default => null,
        };

        if ($nextDate) {
            Maintenance::create([
                'equipment_id' => $maintenance->equipment_id,
                'frequensi' => $maintenance->frequensi,
                'maintenance_date' => $nextDate,
                'status' => 'not due',
            ]);
        }

        return redirect()->route('maintenances.index')->with('success', 'Maintenance closed successfully and new schedule created.');
    }
}
