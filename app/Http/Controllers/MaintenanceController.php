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
use App\Models\NotificationPreference;
use App\Services\NotificationService;

class MaintenanceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('maintenancemenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $maintenancesQuery = Maintenance::query()->oldest('maintenance_date')
            ->with(['equipment.item', 'equipment.store', 'staff', 'confirm'])
            ->whereNotIn('status', ['completed', 'cancelled']);

        $maintenancesQuery->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('frequensi', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('maintenance_date', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
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

        if (!$isMaster) {
            if ($user->department_id) {
                $maintenancesQuery->whereHas('equipment.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $maintenancesQuery->whereNull('id');
            }
        }

        $maintenancesQuery->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('maintenance_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        });

        if ($perPage === 'all') {
            $maintenances = $maintenancesQuery->orderBy('maintenance_date', 'desc')->get();
        } else {
            $maintenances = $maintenancesQuery->orderBy('maintenance_date', 'desc')
                ->paginate((int) $perPage);
            $maintenances->appends(compact('search', 'perPage', 'startDate', 'endDate'));
        }

        return view('maintenances.index', compact('maintenances', 'perPage', 'search', 'startDate', 'endDate'));
    }
    public function tbody(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Maintenance::query()->oldest('maintenance_date')
            ->with(['equipment.item', 'equipment.store', 'staff', 'confirm'])
            ->whereNotIn('status', ['completed', 'cancelled']);

        // Pencarian
        $query->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('frequensi', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('maintenance_date', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
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

        // Filter department
        if (!$isMaster && $user->department_id) {
            $query->whereHas('equipment.item', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // Filter tanggal
        if ($startDate && $endDate) {
            $query->whereBetween('maintenance_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Handle perPage
        if ($perPage === 'all') {
            $maintenances = $query->orderBy('maintenance_date', 'desc')->get();
        } else {
            $maintenances = $query->orderBy('maintenance_date', 'desc')->paginate((int) $perPage);
        }

        return view('partials.maintenances-tbody', compact('maintenances', 'perPage'));
    }
    public function lastUpdated()
    {
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Maintenance::query()->whereNotIn('status', ['completed', 'cancelled']);

        if (!$isMaster && $user->department_id) {
            $query->whereHas('equipment.item', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $lastUpdated = $query->max('updated_at');

        return response()->json(['last_updated' => $lastUpdated]);
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

        $maintenancesQuery = Maintenance::query()->latest('maintenance_date')
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
        if ($perPage === 'all') {
            $maintenances = $maintenancesQuery->orderBy('updated_at', 'desc')->get();
        } else {
            $maintenances = $maintenancesQuery->orderBy('updated_at', 'desc')
                ->paginate((int) $perPage)
                ->appends(request()->query());
        }

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

        $maintenance->load(['equipment.item', 'staff', 'confirm', 'usedSpareParts.sparepart.item']);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Jika bukan master, cek role 2/3/4 boleh akses sesuai departemen
        if (!$isMaster) {
            if (in_array($user->role_id, [2, 3, 4])) {
                if (!$user->department_id || $maintenance->equipment->item->department_id !== $user->department_id) {
                    abort(403, 'You are not authorized to view this maintenance.');
                }
            } else {
                // Role selain 2-4 tidak boleh akses (misal role_id 5 / user biasa)
                abort(403, 'You are not authorized to view this maintenance.');
            }
        }

        // Spareparts filtering
        if ($isMaster) {
            $spareparts = Sparepart::with('item')->get();
        } else {
            $spareparts = Sparepart::with('item')
                ->whereHas('item', function ($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                })
                ->get();
        }

        return view('maintenances.show', compact('maintenance', 'spareparts'));
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

    public function edit($id)
    {
        $this->authorize('schedulemaintenance.edit');
        $maintenance = Maintenance::with('equipment.item')->findOrFail($id);
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (!$isMaster) {
            if (in_array($user->role_id, [2, 3, 4])) {
                $item = $maintenance->equipment?->item;
                if (!$item || $item->department_id !== $user->department_id) {
                    abort(403, 'You are not authorized to edit this maintenance schedule.');
                }
            } else {
                abort(403, 'You are not authorized to edit this maintenance schedule.');
            }
        }
        if ($maintenance->status !== 'not due') {
            abort(403, "Cant edit if status '$maintenance->status'.");
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

        $daysDiff = Carbon::now()->diffInDays($maintenance->maintenance_date, false);

        // Update status berdasarkan selisih hari
        if ($daysDiff <= 1) {
            $maintenance->status = 'maintenance';
        } else {
            $maintenance->status = 'not due';
        }

        $maintenance->save();

        $equipment = $maintenance->equipment;
        $item = $equipment?->item;
        $departmentId = $item?->department_id;
        $maintenanceCode = 'MNT-' . $maintenance->id;

        $targets = $this->getNotificationTargets('edit_maintenance', $departmentId);

        $title = 'Maintenance Edited';
        $message = "Schedule Maintenance <b>{$maintenanceCode}</b> has been <span class='font-bold'>edited</span> by <b>" . Auth::user()->name . "</b>.";

        NotificationService::send(
            $targets,
            'edit_maintenance',
            $title,
            $message,
            'maintenances',
            $maintenance->id
        );

        return redirect()->route('maintenances.index')->with('success', 'Maintenance updated successfully.');
    }
    public function proses($id)
    {
        $this->authorize('maintenance.proses');
        $maintenance = Maintenance::with('equipment.item')->findOrFail($id);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (strtolower($maintenance->status) !== 'maintenance') {
            abort(403, 'Only maintenance with status "maintenance" can be started.');
        }

        if (!$isMaster) {
            if (in_array($user->role_id, [2, 3, 4])) {
                $item = $maintenance->equipment?->item;
                if (!$item || $item->department_id !== $user->department_id) {
                    abort(403, 'You are not authorized to process this maintenance.');
                }
            } else {
                abort(403, 'You are not authorized to process this maintenance.');
            }
        }

        if ($maintenance->status !== 'maintenance') {
            // If the status is not 'in progress', abort with a 404 error and a custom message.
            abort(403, 'No maintenance needed.');
        }
        $maintenance->status = 'in progress';
        $maintenance->picstaff = Auth::id(); // pastikan login user adalah staff

        $maintenance->save();

        $equipment = $maintenance->equipment;
        $item = $equipment?->item;
        $departmentId = $item?->department_id;
        $maintenanceCode = 'MNT-' . $maintenance->id;

        $targets = $this->getNotificationTargets('proses_maintenance', $departmentId);

        $title = 'Maintenance In Progress';
        $message = "Maintenance <b>{$maintenanceCode}</b> is now <span class='font-bold'>in progress</span> by <b>" . Auth::user()->name . "</b>.";

        NotificationService::send(
            $targets,
            'proses_maintenance',
            $title,
            $message,
            'maintenances',
            $maintenance->id
        );

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

        if (strtolower($maintenance->status) !== 'in progress') {
            abort(403, 'Only incidents with status "in progress" can be started.');
        }

        if (!$isMaster) {
            if (in_array($user->role_id, [2, 3, 4])) {
                $item = $maintenance->equipment?->item;
                if (!$item || $item->department_id !== $user->department_id) {
                    abort(403, 'You are not authorized to confirm this maintenance.');
                }
            } else {
                // role_id 5 or others not allowed
                abort(403, 'You are not authorized to confirm this maintenance.');
            }
        }

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
                $equipment = $maintenance->equipment;
                $item = $equipment?->item;
                $location = $equipment?->store?->name ?? '-';
                $departmentId = $item?->department_id;
                $maintenanceCode = 'MNT-' . $maintenance->id;

                $targets = $this->getNotificationTargets('confirm_maintenance', $departmentId);

                $title = 'Maintenance Confirmation';
                $message = "Maintenance <b>{$maintenanceCode}</b> for <b>" . strtoupper($item->name) . "</b> at <b>" . $location . "</b> has already <span class='font-bold'>completed</span> by <b>" . Auth::user()->name . "</b>, Lets closed this maintenance.";

                NotificationService::send(
                    $targets,
                    'confirm_maintenance',
                    $title,
                    $message,
                    'maintenances',
                    $maintenance->id
                );

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
            $equipment = $maintenance->equipment;
            $item = $equipment?->item;
            $departmentId = $item?->department_id;
            $maintenanceCode = 'MNT-' . $maintenance->id;

            $targets = $this->getNotificationTargets('update_spareparts', $departmentId);

            $title = 'Spareparts Updated';
            $message = "Spareparts for <b>maintenance {$maintenanceCode}</b> have been <span class='font-bold'>edited</span> by <b>" . Auth::user()->name . "</b>.";

            NotificationService::send(
                $targets,
                'update_spareparts',
                $title,
                $message,
                'maintenances',
                $maintenance->id
            );

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
        $maintenance = Maintenance::with('equipment.item')->findOrFail($id);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (strtolower($maintenance->status) !== 'resolved') {
            abort(403, 'Only incidents with status "resolved" can be closed.');
        }

        if (!$isMaster) {
            if (in_array($user->role_id, [2, 3, 4])) {
                $item = $maintenance->equipment?->item;
                if (!$item || $item->department_id !== $user->department_id) {
                    abort(403, 'You are not authorized to close this maintenance.');
                }
            } else {
                abort(403, 'You are not authorized to close this maintenance.');
            }
        }

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

        $equipment = $maintenance->equipment;
        $item = $equipment?->item;
        $departmentId = $item?->department_id;
        $maintenanceCode = 'MNT-' . $maintenance->id;

        $targets = $this->getNotificationTargets('closed_maintenance', $departmentId);

        $title = 'Maintenance Closed';
        $message = "Maintenance <b>{$maintenanceCode}</b> has been <span class='font-bold'>closed</span> by <b>" . Auth::user()->name . "</b>, and the next schedule has been created.";;

        NotificationService::send(
            $targets,
            'closed_maintenance',
            $title,
            $message,
            'maintenances',
            $maintenance->id
        );

        return redirect()->route('maintenances.index')->with('success', 'Maintenance closed successfully and new schedule created.');
    }
}
