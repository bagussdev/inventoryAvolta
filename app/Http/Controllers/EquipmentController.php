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
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                if ($user->store_location) {
                    $equipmentsQuery->where('location', $user->store_location);
                } else {
                    $equipmentsQuery->whereRaw('1=0');
                }
            }
        }

        if ($search) {
            $equipmentsQuery->where(function ($q) use ($search) {
                $q->whereHas('item', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('store', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('transactions_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($perPage === 'all') {
            $equipments = $equipmentsQuery->orderBy('created_at', 'desc')->get();
        } else {
            $equipments = $equipmentsQuery->orderBy('created_at', 'desc')
                ->paginate((int)$perPage)
                ->appends([
                    'search' => $search,
                    'per_page' => $perPage,
                ]);
        }

        return view('equipments.index', compact('equipments', 'search', 'perPage'));
    }

    public function tbody(Request $request)
    {
        // $this->authorize('equipmentsmenu');

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $equipmentsQuery = Equipment::with(['item', 'store']);

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                if ($user->store_location) {
                    $equipmentsQuery->where('location', $user->store_location);
                } else {
                    $equipmentsQuery->whereRaw('1=0');
                }
            }
        }

        if ($search) {
            $equipmentsQuery->where(function ($q) use ($search) {
                $q->whereHas('item', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('store', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('transactions_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($perPage === 'all') {
            $equipments = $equipmentsQuery->orderBy('created_at', 'desc')->get();
        } else {
            $equipments = $equipmentsQuery->orderBy('created_at', 'desc')->paginate((int)$perPage);
        }

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

        if (!$isMaster && $user->store_location !== $equipment->location) {
            abort(403, 'Unauthorized access to this equipment');
        }

        return view('equipments.show', compact('equipment'));
    }

    public function showMigrateForm(Equipment $equipment)
    {
        $this->authorize('equipments.migrate');
        $stores = Store::where('id', '!=', $equipment->store_id)->get();
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
        ];

        if ($newStore->type === 'Store') {
            $rules['frequensi'] = 'required|in:weekly,monthly';
        }

        $validator = Validator::make($request->all(), $rules, [
            'frequensi.required' => 'Masukkan data frekuensi.',
            'frequensi.in' => 'Frekuensi harus weekly atau monthly.',
            'store_id.required' => 'Store tujuan wajib diisi.',
            'store_id.exists' => 'Store tujuan tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($currentStore && $currentStore->type === 'Store' && $newStore->type === 'Store' && $currentStore->id !== $newStore->id) {
        } elseif ($newStore->type === 'Store') {
            $equipment->status = 'used';
        } elseif ($newStore->name === 'Storage Center' && $newStore->type === 'Warehouse') {
            $equipment->status = 'available';
        } elseif ($newStore->name === 'Service Center') {
            $equipment->status = 'maintenance';
        }

        $equipment->location = $request->store_id;
        $equipment->save();


        if ($newStore->name === 'Service Center' || ($newStore->name === 'Storage Center' && $newStore->type === 'Warehouse')) {

            $activeMaintenance = Maintenance::where('equipment_id', $equipment->id)
                ->where('status', 'not due')
                ->first();

            if ($activeMaintenance) {
                $activeMaintenance->status = 'cancelled';
                $activeMaintenance->notes = 'Maintenance cancelled due to equipment migration to ' . $newStore->name . ' from ' . ($currentStore ? $currentStore->name : 'Unknown Location') . '.';
                $activeMaintenance->confirmby = Auth::id();
                $activeMaintenance->save();
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
