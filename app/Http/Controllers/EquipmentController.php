<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\Store;
use Illuminate\Support\Facades\Gate;
use \Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\NotificationPreference;
use App\Services\NotificationService;

class EquipmentController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('equipmentsmenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $equipmentsQuery = Equipment::with(['item', 'store']);

        if (!$isMaster) {
            switch ($user->role_id) {
                case 5: // User
                    if ($user->store_location) {
                        $equipmentsQuery->where('location', $user->store_location);
                    } else {
                        $equipmentsQuery->whereRaw('1=0');
                    }
                    break;

                case 2: // Manager
                case 3: // SPV
                case 4: // Staff
                    if ($user->department_id) {
                        $equipmentsQuery->whereHas('item', function ($q) use ($user) {
                            $q->where('department_id', $user->department_id);
                        });
                    } else {
                        $equipmentsQuery->whereRaw('1=0');
                    }
                    break;

                default:
                    $equipmentsQuery->whereRaw('1=0');
                    break;
            }
        }

        if ($search) {
            $equipmentsQuery->where(function ($q) use ($search) {
                $q->whereHas('item', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('store', fn($q3) => $q3->where('name', 'like', "%{$search}%"))
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('transactions_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $equipments = $perPage === 'all'
            ? $equipmentsQuery->orderBy('created_at', 'desc')->get()
            : $equipmentsQuery->orderBy('created_at', 'desc')
            ->paginate((int) $perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]);

        return view('equipments.index', compact('equipments', 'search', 'perPage'));
    }


    public function tbody(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $equipmentsQuery = Equipment::with(['item', 'store']);

        if (!$isMaster) {
            switch ($user->role_id) {
                case 5:
                    if ($user->store_location) {
                        $equipmentsQuery->where('location', $user->store_location);
                    } else {
                        $equipmentsQuery->whereRaw('1=0');
                    }
                    break;

                case 2:
                case 3:
                case 4:
                    if ($user->department_id) {
                        $equipmentsQuery->whereHas('item', function ($q) use ($user) {
                            $q->where('department_id', $user->department_id);
                        });
                    } else {
                        $equipmentsQuery->whereRaw('1=0');
                    }
                    break;

                default:
                    $equipmentsQuery->whereRaw('1=0');
                    break;
            }
        }

        if ($search) {
            $equipmentsQuery->where(function ($q) use ($search) {
                $q->whereHas('item', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('store', fn($q3) => $q3->where('name', 'like', "%{$search}%"))
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('transactions_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $equipments = $perPage === 'all'
            ? $equipmentsQuery->orderBy('created_at', 'desc')->get()
            : $equipmentsQuery->orderBy('created_at', 'desc')->paginate((int) $perPage);

        return view('partials.equipments-tbody', compact('equipments'));
    }

    public function lastUpdated()
    {
        $lastUpdated = Equipment::max('updated_at');
        return response()->json(['last_updated' => $lastUpdated]);
    }

    public function show($id)
    {
        $equipment = Equipment::with([
            'item',
            'store',
            'transaction',
            'maintenances' => fn($q) => $q->latest(),
            'incidents' => fn($q) => $q->latest(),
        ])->findOrFail($id);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (!$isMaster) {
            if ($user->role_id === 5) {
                // Role user biasa: filter berdasarkan lokasi
                if ($user->store_location !== $equipment->location) {
                    abort(403, 'Unauthorized: This equipment is not at your store.');
                }
            } elseif (in_array($user->role_id, [2, 3, 4])) {
                // Role manager, spv, staff: filter berdasarkan department
                if (!$equipment->item || $user->department_id !== $equipment->item->department_id) {
                    abort(403, 'Unauthorized: You are not allowed to view equipment from another department.');
                }
            } else {
                // Selain itu, tolak akses
                abort(403, 'Unauthorized: Role not allowed.');
            }
        }

        return view('equipments.show', compact('equipment'));
    }


    public function showMigrateForm(Equipment $equipment)
    {
        $this->authorize('equipments.migrate');
        $stores = Store::where('id', '!=', $equipment->location)->where('type', '!=', 'Office')->get();
        return view('equipments.migrate', compact('equipment', 'stores'));
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

    public function storeMigrate(Request $request, Equipment $equipment)
    {
        $newStore = Store::find($request->store_id);

        if (!$newStore) {
            return redirect()->back()->with('error', 'Target store not found.');
        }

        $currentStore = $equipment->store;

        $rules = [
            'store_id' => 'required|exists:store,id',
            'frequensi' => 'nullable|in:weekly,monthly',
            'alias' => 'nullable|string|max:50',
        ];

        // Jika store baru bertipe "Store", alias wajib
        if ($newStore->type === 'Store') {
            $rules['alias'] = 'required|string|max:50'; // alias now required
            $rules['frequensi'] = 'required|in:weekly,monthly';
        }

        $validator = Validator::make($request->all(), $rules, [
            'frequensi.required' => 'Masukkan data frekuensi.',
            'frequensi.in' => 'Frekuensi harus weekly atau monthly.',
            'store_id.required' => 'Store tujuan wajib diisi.',
            'store_id.exists' => 'Store tujuan tidak ditemukan.',
            'alias.required' => 'Alias wajib diisi saat migrasi ke tipe Store.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Set status equipment
        if ($currentStore && $currentStore->type === 'Store' && $newStore->type === 'Store' && $currentStore->id !== $newStore->id) {
            // Pindah antar store (status tidak berubah)
        } elseif ($newStore->type === 'Store') {
            $equipment->status = 'used';
        } elseif ($newStore->name === 'Storage Center' && $newStore->type === 'Warehouse') {
            $equipment->status = 'available';
        } elseif ($newStore->name === 'Service Center') {
            $equipment->status = 'maintenance';
        } elseif ($newStore->name === 'Scrap Center') {
            $equipment->status = 'broken';
        }

        // Simpan migrasi
        $equipment->location = $request->store_id;

        // Alias logic
        if ($newStore->type === 'Store') {
            $equipment->alias = $request->alias;
        } else {
            $equipment->alias = null;
        }

        $equipment->save();

        if (
            $newStore->name === 'Service Center' ||
            ($newStore->name === 'Storage Center' && $newStore->type === 'Warehouse') ||
            $newStore->name === 'Scrap Center'
        ) {
            $activeMaintenances = Maintenance::where('equipment_id', $equipment->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->get();

            foreach ($activeMaintenances as $maintenance) {
                $maintenance->status = 'cancelled';
                $maintenance->notes = 'Maintenance cancelled due to equipment migration to ' . $newStore->name . ' from ' . ($currentStore?->name ?? 'Unknown Location') . '.';
                $maintenance->confirmby = Auth::id();
                $maintenance->save();
            }
        } elseif ($newStore->type === 'Store') {
            $isSameTypeStoreMigration = $currentStore && $currentStore->type === 'Store' && $newStore->type === 'Store';

            if ($isSameTypeStoreMigration) {
            } else {
                $existingNotDueMaintenance = Maintenance::where('equipment_id', $equipment->id)
                    ->where('status', 'not due')
                    ->first();

                $days = $request->frequensi === 'weekly' ? 7 : 30;

                if (!$existingNotDueMaintenance) {
                    Maintenance::create([
                        'equipment_id' => $equipment->id,
                        'frequensi' => $request->frequensi,
                        'maintenance_date' => Carbon::now()->addDays($days),
                        'status' => 'not due',
                    ]);
                } else {
                    $existingNotDueMaintenance->frequensi = $request->frequensi;
                    $existingNotDueMaintenance->maintenance_date = Carbon::now()->addDays($days);
                    $existingNotDueMaintenance->save();
                }
            }
        }

        $user = Auth::user();
        $targets = $this->getNotificationTargets('equipment_migrate', $equipment->department_id);

        NotificationService::send(
            $targets,
            'equipment',
            'Equipment Migrated',
            'Equipment "' . $equipment->item->name . '" has been migrated by ' . $user->name . ' to ' . $newStore->name . '.',
            'equipments',
            $equipment->id
        );

        return redirect()->route('equipments.index')->with('success', 'Equipment migrated successfully and status updated.');
    }
}
