<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncidentsExport;
use App\Exports\IncidentsCompletedExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Equipment;
use App\Models\Department;
use App\Models\Store;
use App\Models\Item;
use App\Models\User;
use App\Models\Sparepart;
use App\Models\UsedSparepart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\NotificationPreference;
use App\Services\NotificationService;

class IncidentController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('incidentmenu');

        $perPage    = $request->input('per_page', 5);
        $search     = $request->input('search');
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $incidentsQuery = Incident::query()->oldest('created_at')
            // Eager load all relationships that might be needed for display or filtering
            ->with([
                'equipment',         // Direct relation for 'item_problem'
                'store',        // Direct relation for 'location'
                'user',         // Relation for 'pic_user'
                'picUser',      // Relation for 'pic_staff'
                'confirm',      // Relation for 'confirm_by'
                'resolve'       // Relation for 'resolvedby'
            ])->whereNotIn('status', ['completed']);

        // Filter: Global Search
        if ($search) {
            $incidentsQuery->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('message_user', 'like', "%{$search}%")
                    ->orWhere('message_staff', 'like', "%{$search}%")
                    // Search through related item details (direct relation from Incident)
                    ->orWhereHas('equipment.item', function ($sub) use ($search) { // Changed from 'equipment.item' to 'item'
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    })
                    // Search through store name
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('site_code', 'like', "%{$search}%");
                    })
                    // Search through reporter's name (user relation maps to pic_user)
                    ->orWhereHas('user', function ($sub) use ($search) { // Refers to 'pic_user' column
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Search through PIC Staff's name
                    ->orWhereHas('picUser', function ($sub) use ($search) { // Refers to 'pic_staff' column
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Search through Confirm By user's name
                    ->orWhereHas('confirm', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Search through Resolved By user's name
                    ->orWhereHas('resolve', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                if ($user->store_location) {
                    $incidentsQuery->where('location', $user->store_location);
                } else {
                    abort(403, 'You are not authorized to access incidents without a store location.');
                }
            } else {
                if ($user->department_id) {
                    $incidentsQuery->where('department_to', $user->department_id);
                } else {
                    abort(403, 'You are not authorized to access incidents without a department.');
                }
            }
        }

        // Filter: Tanggal (NO CHANGE)
        if ($startDate && $endDate) {
            $incidentsQuery->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59'
            ]);
        }


        $incidents = $perPage === 'all'
            ? $incidentsQuery->latest('created_at')->get()
            : $incidentsQuery->latest('created_at')->paginate((int) $perPage)->appends($request->query());

        return view('incidents.index', compact('incidents', 'perPage', 'search', 'startDate', 'endDate'));
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

    public function tbody(Request $request)
    {
        $perPage    = $request->input('per_page', 5);
        $search     = $request->input('search');
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Incident::with([
            'equipment.item',
            'store',
            'user',
            'picUser',
            'confirm',
            'resolve'
        ])->oldest('created_at')->whereNotIn('status', ['completed']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('message_user', 'like', "%{$search}%")
                    ->orWhere('message_staff', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('site_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('picUser', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('confirm', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('resolve', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                if ($user->store_location) {
                    $query->where('location', $user->store_location);
                } else {
                    abort(403, 'You are not authorized to access incidents without a store location.');
                }
            } else {
                if ($user->department_id) {
                    $query->where('department_to', $user->department_id);
                } else {
                    abort(403, 'You are not authorized to access incidents without a department.');
                }
            }
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        if ($perPage === 'all') {
            $incidents = $query->latest('created_at')->get();
        } else {
            $incidents = $query->latest('created_at')->paginate((int) $perPage);
        }

        return view('partials.incidents-tbody', compact('incidents', 'perPage'));
    }
    public function lastUpdated()
    {
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = Incident::query();

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                if ($user->store_location) {
                    $query->where('location', $user->store_location);
                } else {
                    $query->whereRaw('1=0');
                }
            } else {
                if ($user->department_id) {
                    $query->whereHas('equipment.item', function ($q) use ($user) {
                        $q->where('department_id', $user->department_id);
                    });
                } else {
                    $query->whereRaw('1=0');
                }
            }
        }

        $lastUpdated = $query->max('updated_at');

        return response()->json(['last_updated' => $lastUpdated]);
    }

    public function export(Request $request)
    {
        // Otorisasi
        $this->authorize('incidentmenu');

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $incidentsQuery = Incident::query()
            ->with(['equipment.item', 'equipment.store', 'user'])->whereNotIn('status', ['completed']);

        // 1. Filter Pencarian
        if ($search) {
            $incidentsQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment.store', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $incidentsQuery->whereHas('equipment.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $incidentsQuery->whereNull('id');
            }
        }

        // 3. Filter Tanggal
        if ($startDate && $endDate) {
            $incidentsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $filteredIncidents = $incidentsQuery->latest('created_at')->get();

        $filename = 'incidents_filtered_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new IncidentsExport($filteredIncidents), $filename);
    }
    public function completed(Request $request)
    {
        $this->authorize('incidentmenu');

        $perPage    = $request->input('per_page', 5);
        $search     = $request->input('search');
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $incidentsQuery = Incident::query()
            // Eager load all relationships that might be needed for display or filtering
            ->with([
                'equipment',         // Direct relation for 'item_problem'
                'store',        // Direct relation for 'location'
                'user',         // Relation for 'pic_user'
                'picUser',      // Relation for 'pic_staff'
                'confirm',      // Relation for 'confirm_by'
                'resolve'       // Relation for 'resolvedby'
            ])->whereIn('status', ['completed']);

        // Filter: Global Search
        if ($search) {
            $incidentsQuery->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('message_user', 'like', "%{$search}%")
                    ->orWhere('message_staff', 'like', "%{$search}%")
                    // Search through related item details (direct relation from Incident)
                    ->orWhereHas('equipment.item', function ($sub) use ($search) { // Changed from 'equipment.item' to 'item'
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    })
                    // Search through store name
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('site_code', 'like', "%{$search}%");
                    })
                    // Search through reporter's name (user relation maps to pic_user)
                    ->orWhereHas('user', function ($sub) use ($search) { // Refers to 'pic_user' column
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Search through PIC Staff's name
                    ->orWhereHas('picUser', function ($sub) use ($search) { // Refers to 'pic_staff' column
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Search through Confirm By user's name
                    ->orWhereHas('confirm', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Search through Resolved By user's name
                    ->orWhereHas('resolve', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                // Filter berdasarkan lokasi store user
                if ($user->store_location) {
                    $incidentsQuery->where('location', $user->store_location);
                } else {
                    $incidentsQuery->whereRaw('1=0'); // Tidak ada lokasi
                }
            } else {
                // Filter berdasarkan departemen item
                if ($user->department_id) {
                    $incidentsQuery->whereHas('equipment.item', function ($q) use ($user) {
                        $q->where('department_id', $user->department_id);
                    });
                } else {
                    $incidentsQuery->whereRaw('1=0'); // Tidak ada department
                }
            }
        }

        // Filter: Tanggal (NO CHANGE)
        if ($startDate && $endDate) {
            $incidentsQuery->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59'
            ]);
        }

        if ($perPage === 'all') {
            $incidents = $incidentsQuery->latest('created_at')->get();
        } else {
            $incidents = $incidentsQuery->latest('created_at')
                ->paginate((int) $perPage)
                ->appends($request->query());
        }

        return view('incidents.completed', compact('incidents', 'perPage', 'search', 'startDate', 'endDate'));
    }
    public function exportCompleted(Request $request)
    {
        // Otorisasi
        $this->authorize('incidentmenu');

        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $incidentsQuery = Incident::query()
            ->with(['equipment.item', 'equipment.store', 'user'])->whereIn('status', ['completed']);

        // 1. Filter Pencarian
        if ($search) {
            $incidentsQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('equipment.item', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment.store', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Filter Department (jika bukan Master)
        if (!$isMaster) {
            if ($user->department_id) {
                $incidentsQuery->whereHas('equipment.item', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $incidentsQuery->whereNull('id');
            }
        }

        // 3. Filter Tanggal
        if ($startDate && $endDate) {
            $incidentsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $filteredIncidents = $incidentsQuery->latest('created_at')->get();

        $filename = 'incidentsCompleted_filtered_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new IncidentsCompletedExport($filteredIncidents), $filename);
    }
    public function create()
    {
        $this->authorize('incident.create');

        $loggedInUser = Auth::user();
        $userRole = strtolower($loggedInUser->role->name ?? '');
        $isMaster = Gate::allows('isMaster');

        // Ambil semua store dengan tipe 'store' (untuk dropdown store)
        $storesQuery = Store::query();
        if (in_array($userRole, ['staff', 'mep', 'spv'])) {
            $storesQuery->where('type', 'store');
        }
        $stores = $storesQuery->get();

        // Ambil semua equipments (untuk filter item_problem nanti)
        $equipmentsQuery = Equipment::with(['item', 'store']);
        if (!$isMaster && $loggedInUser->department_id) {
            $equipmentsQuery->whereHas('item', function ($q) use ($loggedInUser) {
                $q->where('department_id', $loggedInUser->department_id);
            });
        }
        $equipments = $equipmentsQuery->get();

        // Ambil semua items (untuk keperluan select item_problem - disimpan sebagai items_id)
        $items = Item::all();

        // Ambil id departemen IT dan MEP
        $deptItId = Department::where('name', 'IT')->value('id');
        $deptMepId = Department::where('name', 'MEP')->value('id');

        return view('incidents.create', compact(
            'stores',
            'equipments',
            'items',
            'loggedInUser',
            'deptItId',
            'deptMepId'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('incident.create');

        $validated = $request->validate([
            'store_id'        => 'required|exists:store,id',
            'department_to'   => 'required|exists:departments,id',
            'item_problem'    => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'others' && !Equipment::where('id', $value)->exists()) {
                    $fail('The selected item problem is invalid.');
                }
            }],
            'message_user'    => 'required|string|max:1000',
            'attachment_user' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
            'item_description' => $request->item_problem === 'others' ? 'required|string|max:50' : 'nullable',
        ]);

        $loggedInUser = Auth::id();
        if ($request->item_problem === 'others') {
            $request->validate([
                'item_description' => 'required|string|max:50',
            ]);
        }
        if ($validated['item_problem'] !== 'others') {
            // Cek duplikat untuk item dari equipment
            $existing = Incident::where('item_problem', $validated['item_problem'])
                ->whereIn('status', ['waiting', 'in progress', 'pending', 'resolved'])
                ->exists();

            if ($existing) {
                return back()->withErrors([
                    'item_problem' => 'This item is already reported and is still being handled.'
                ])->withInput();
            }
        } else {
            // Cek duplikat untuk item dari description (Others)
            $existing = Incident::where('item_problem', null)
                ->where('item_description', $request->item_description)
                ->whereIn('status', ['waiting', 'in progress', 'pending', 'resolved'])
                ->exists();

            if ($existing) {
                return back()->withErrors([
                    'item_description' => 'This item description is already reported and still being handled.'
                ])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // LOCK untuk dapatkan unique ID terakhir secara aman
            $last = DB::table('incidents')
                ->select('unique_id')
                ->where('unique_id', 'like', 'INC%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $lastNum = $last ? (int) Str::after($last->unique_id, 'INC') : 0;
            $newUniqueId = 'INC' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

            // Upload file
            $attachmentPath = $request->file('attachment_user')->store('incident_attachments', 'public');

            // Simpan data incident
            $incident = Incident::create([
                'unique_id'       => $newUniqueId,
                'location'        => $validated['store_id'],
                'department_to'   => $validated['department_to'],
                'item_problem'    => $validated['item_problem'] !== 'others' ? $validated['item_problem'] : null,
                'item_description' => $validated['item_problem'] === 'others' ? strtolower($request->item_description) : null,
                'message_user'    => $validated['message_user'],
                'attachment_user' => $attachmentPath,
                'pic_user'        => $loggedInUser,
                'status'          => 'waiting',
            ]);

            $user = Auth::user();

            $item = $incident->item?->name ?? ($incident->item_description ?: '-');
            $location = $incident->location->name ?? '-';

            $title = 'New Incident Reported';
            $message = "New incident <b>{$incident->unique_id}</b> has been reported by <b>{$user->name}</b> for item <b>{$item}</b> at <b>{$location}</b>.";

            $targets = $this->getNotificationTargets('create_incident', $incident->department_to);

            NotificationService::send(
                $targets,
                'create_incident',
                $title,
                $message,
                'incidents',
                $incident->id
            );

            DB::commit();

            return redirect()->route('incidents.index')->with('success', 'Incident created successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->withErrors([
                    'unique_id' => 'Duplicate ID generated. Please try again.'
                ]);
            }

            return back()->withErrors(['error' => 'Unexpected error: ' . $e->getMessage()]);
        }
    }

    public function checkIncidentStatus($equipmentId)
    {
        $isReported = Incident::where('item_problem', $equipmentId)
            ->whereIn('status', ['waiting', 'in progress', 'pending'])
            ->exists();

        return response()->json(['active' => $isReported]);
    }

    public function getItemsByStore($storeId, $departmentId)
    {
        try {
            $items = Equipment::where('location', $storeId)
                ->whereHas('item', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->with('item:id,name,model,brand') // hanya ambil field penting
                ->get()
                ->map(function ($equipment) {
                    return [
                        'id' => $equipment->id, // <-- ini id dari equipment (bukan item)
                        'name' => $equipment->item->name ?? '-',
                        'alias' => $equipment->alias ?? '-',
                        'brand' => $equipment->item->brand ?? '-',
                    ];
                })
                ->values(); // reset keys

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch items', 'message' => $e->getMessage()], 500);
        }
    }

    public function start($id)
    {
        $incident = Incident::findOrFail($id);
        $this->authorize('incident.proses', $incident);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (strtolower($incident->status) !== 'waiting') {
            abort(403, 'Only incidents with status "waiting" can be started.');
        }

        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $incident->department_to) {
                abort(403, 'You are not authorized to access incidents outside your department.');
            }
        }
        $incident->update([
            'status' => 'in progress',
            'pic_staff' => Auth::id()
        ]);

        $item = $incident->item?->name ?? ($incident->item_description ?: '-');
        $location = $incident->store?->name ?? '-';

        $title = 'Incident In Progress';
        $message = "Incident <b>{$incident->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> is now being handled by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('start_incident', $incident->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $incident->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'start_incident',
            $title,
            $message,
            'incidents',
            $incident->id
        );


        return redirect()->route('incidents.index')->with('success', 'Incident marked as In Progress.');
    }
    public function restart($id)
    {
        $incident = Incident::findOrFail($id);
        $this->authorize('incident.proses', $incident);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // ⛔ Hanya status 'pending' yang boleh di-restart
        if (strtolower($incident->status) !== 'pending') {
            abort(403, 'Only incidents with status "pending" can be restarted.');
        }

        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $incident->department_to) {
                abort(403, 'You can only restart incidents within your department.');
            }
        }

        $incident->update([
            'status' => 'in progress',
        ]);

        $user = Auth::user();
        $item = $incident->item?->name ?? ($incident->item_description ?: '-');
        $location = $incident->store?->name ?? '-';

        $title = 'Incident Restarted';
        $message = "Incident <b>{$incident->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been restarted by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('restart_incident', $incident->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $incident->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'restart_incident',
            $title,
            $message,
            'incidents',
            $incident->id
        );

        return redirect()->route('incidents.index')->with('success', 'Incident marked as In Progress.');
    }

    public function pending(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $this->authorize('incident.pending', $incident);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // ⛔ Hanya status 'in progress' yang bisa di-pending
        if (strtolower($incident->status) !== 'in progress') {
            abort(403, 'Only incidents with status "in progress" can be marked as pending.');
        }

        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $incident->department_to) {
                abort(403, 'You can only mark incidents from your department as pending.');
            }
        }

        $incident->update([
            'status' => 'pending',
            'message_staff' => $request->notes,
        ]);

        $user = Auth::user();
        $item = $incident->item?->name ?? ($incident->item_description ?: '-');
        $location = $incident->store?->name ?? '-';

        $title = 'Incident Marked as Pending';
        $message = "Incident <b>{$incident->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been marked as <b>Pending</b> by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('pending_incident', $incident->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $incident->store->id; // PENTING
        }
        unset($target);
        NotificationService::send(
            $targets,
            'pending_incident',
            $title,
            $message,
            'incidents',
            $incident->id
        );

        return redirect()->route('incidents.index')->with('success', 'Incident marked as Pending.');
    }

    public function resolve($id)
    {
        $this->authorize('incident.resolve');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $incident = Incident::with([
            'equipment.item.department',
            'store',
            'user',
            'picUser',
            'equipment.item',
            'department',
            'confirm',
            'usedSpareParts.sparepart.item'
        ])->findOrFail($id);

        // ⛔ Hanya bisa jika status = in progress
        if (strtolower($incident->status) !== 'in progress') {
            abort(403, 'Only incidents with status "in progress" can be resolved.');
        }

        // ⛔ User biasa (role_id 5) tidak boleh akses
        if ($user->role_id === 5) {
            abort(403, 'You are not authorized to resolve this incident.');
        }

        // 🔒 Role 2/3/4 harus cocok departemennya
        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $incident->department_to) {
                abort(403, 'You can only resolve incidents from your own department.');
            }
        }

        // ✅ Ambil spareparts yang masih tersedia dan sesuai departemen
        $sparepartsQuery = Sparepart::where('qty', '>', 0)->with('item');

        if (!$isMaster && $user->department_id) {
            $sparepartsQuery->whereHas('item', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $spareparts = $sparepartsQuery->get();

        return view('incidents.confirm', compact('incident', 'spareparts'));
    }

    public function submitConfirm(Request $request, $id)
    {
        $this->authorize('incident.resolve');

        $request->validate([
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:10240',
            'spareparts' => 'nullable|array',
            'spareparts.*.id' => 'nullable|exists:spareparts,id',
            'spareparts.*.qty' => 'nullable|integer|min:1',
            'spareparts.*.note' => 'nullable|string|max:255',
        ]);

        $incident = Incident::findOrFail($id);

        // Gabungkan spareparts dengan ID yang sama
        $groupedSpareparts = [];
        foreach ($request->spareparts ?? [] as $spare) {
            $id = $spare['id'];
            $qty = $spare['qty'];
            $note = $spare['note'] ?? null;

            if (!isset($groupedSpareparts[$id])) {
                $groupedSpareparts[$id] = [
                    'id' => $id,
                    'qty' => $qty,
                    'note' => $note,
                ];
            } else {
                $groupedSpareparts[$id]['qty'] += $qty;
                if ($note && !str_contains($groupedSpareparts[$id]['note'], $note)) {
                    $groupedSpareparts[$id]['note'] .= '; ' . $note;
                }
            }
        }

        // Validasi stok dulu sebelum simpan
        foreach ($groupedSpareparts as $spare) {
            $sparepart = Sparepart::find($spare['id']);
            if (!$sparepart) continue;

            $availableStock = $sparepart->qty ?? 0;
            if ($spare['qty'] > $availableStock) {
                return back()->with('error', 'Insufficient stock for sparepart: ' . $sparepart->item->name)->withInput();
            }
        }

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment')->store('incident_attachments', 'public');
            $incident->attachment_staff = $file;
        }

        $incident->message_staff = $request->notes;
        $incident->status = 'resolved';
        $incident->resolvedby = Auth::id();
        $incident->resolved_at = now();
        $incident->save();

        // Simpan UsedSpareparts yang sudah digabung
        foreach ($groupedSpareparts as $spare) {
            $sparepart = Sparepart::find($spare['id']);
            if (!$sparepart) continue;

            UsedSparepart::create([
                'spareparts_id' => $spare['id'],
                'incident_id' => $incident->id,
                'qty' => $spare['qty'],
                'note' => $spare['note'] ?? null,
            ]);

            $sparepart->qty -= $spare['qty'];
            $sparepart->status = $sparepart->qty < 0
                ? 'empty'
                : ($sparepart->qty > 5 ? 'available' : 'low');
            $sparepart->save();
        }

        $user = Auth::user();
        $item = $incident->item?->name ?? ($incident->item_description ?: '-');
        $location = $incident->store?->name ?? '-';

        $title = 'Incident Resolved';
        $message = "Incident <b>{$incident->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been <b>resolved</b> by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('resolve_incident', $incident->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $incident->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'resolve_incident',
            $title,
            $message,
            'incidents',
            $incident->id
        );

        return redirect()->route('incidents.index')->with('success', 'Incident resolved and confirmed successfully.');
    }

    public function complete($id)
    {
        $incident = Incident::findOrFail($id);
        $this->authorize('incident.closed', $incident);
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if (strtolower($incident->status) !== 'resolved') {
            abort(403, 'Only incidents with status "resolved" can be completed.');
        }

        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $incident->department_to) {
                abort(403, 'You can only complete incidents from your own department.');
            }
        }

        $incident->update([
            'status' => 'completed',
            'confirmby' => Auth::id(),
        ]);

        $item = $incident->item?->name ?? ($incident->item_description ?: '-');
        $location = $incident->store?->name ?? '-';

        $title = 'Incident Completed';
        $message = "Incident <b>{$incident->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been <b>closed</b> by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('close_incident', $incident->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $incident->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'close_incident',
            $title,
            $message,
            'incidents',
            $incident->id
        );

        return redirect()->route('incidents.index')->with('success', 'Incident marked as Completed.');
    }

    public function show(Incident $incident)
    {
        $this->authorize('incidentmenu');

        $incident->load([
            'equipment.store',
            'equipment.item',
            'user',
            'picUser',
            'confirm',
            'store',
            'usedSpareParts.sparepart.item'
        ]);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // ⛔ User biasa hanya boleh lihat incident sesuai lokasi
        if ($user->role_id === 5) {
            if ($user->store_location !== $incident->location) {
                abort(403, 'You do not have permission to view this incident.');
            }
        }

        // 🔒 Role 2/3/4 hanya boleh lihat incident dari departemen sendiri
        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $incident->department_to) {
                abort(403, 'You can only view incidents from your own department.');
            }
        }

        $spareparts = Sparepart::with('item')->get();

        return view('incidents.show', compact('incident', 'spareparts'));
    }

    public function updateSpareparts(Request $request, $id)
    {
        $this->authorize('incident.update');
        $request->validate([
            'spareparts' => 'nullable|array',
            'spareparts.*.id' => 'required|exists:spareparts,id',
            'spareparts.*.qty' => 'required|integer|min:1',
            'spareparts.*.note' => 'nullable|string|max:255',
            'spareparts.*.used_id' => 'nullable|exists:used_spareparts,id',
            'deleted_ids' => 'nullable|array',
            'deleted_ids.*' => 'exists:used_spareparts,id',
        ]);

        $incident = Incident::with('usedSpareParts')->findOrFail($id);
        $oldUsedById = $incident->usedSpareParts->keyBy('id');
        $newInputs = collect($request->spareparts ?? []);
        $deletedIds = collect($request->deleted_ids ?? []);

        DB::beginTransaction();
        try {
            // Handle deletions
            foreach ($deletedIds as $deletedId) {
                $used = UsedSparepart::find($deletedId);
                if ($used && $used->incident_id === $incident->id) {
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
                    $existingUsed = $incident->usedSpareParts()->where('spareparts_id', $newSparepartId)->first();

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
                            'incident_id' => $incident->id,
                            'qty' => $newQty,
                            'note' => $note,
                        ]);
                    }
                }
            }

            $user = Auth::user();
            $item = $incident->item?->name ?? ($incident->item_description ?: '-');
            $location = $incident->store?->name ?? '-';

            $title = 'Incident Spareparts Updated';
            $message = "Spareparts for incident <b>{$incident->unique_id}</b> (item <b>{$item}</b> at <b>{$location}</b>) have been updated by <b>{$user->name}</b>.";

            $targets = $this->getNotificationTargets('update_sparepart_incident', $incident->department_to);
            foreach ($targets as &$target) {
                $target['store_id'] = $incident->store->id;
            }
            unset($target);
            NotificationService::send(
                $targets,
                'update_sparepart_incident',
                $title,
                $message,
                'incidents',
                $incident->id
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
    public function edit($id)
    {
        $incident = Incident::with(['store', 'department', 'equipment.item'])->findOrFail($id);

        if (strtolower($incident->status) !== 'waiting') {
            abort(403, 'Only incidents with status "waiting" can be edited.');
        }

        $this->authorize('incident.edit', $incident); // Policy

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if ($user->role_id === 5) {
            if ($user->store_location !== $incident->location) {
                abort(403, 'You are not authorized to edit this incident.');
            }
        }

        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            $isPicUser = $incident->pic_user === $user->id;

            if (!$isPicUser && $user->department_id !== $incident->department_to) {
                abort(403, 'You are only allowed to edit incidents from your department or if you are the PIC.');
            }
        }


        return view('incidents.edit', compact('incident'));
    }


    public function update(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $this->authorize('incident.edit', $incident);

        $validated = $request->validate([
            'attachment_user' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480',
            'message_user' => 'required|string|max:1000',
        ]);

        // Hapus file lama jika ada dan upload baru
        if ($request->hasFile('attachment_user')) {
            if ($incident->attachment_user && Storage::disk('public')->exists($incident->attachment_user)) {
                Storage::disk('public')->delete($incident->attachment_user);
            }

            $path = $request->file('attachment_user')->store('incident_attachments', 'public');
            $incident->attachment_user = $path;
        }

        $incident->message_user = $validated['message_user'];
        $incident->save();

        $user = Auth::user();
        $item = $incident->item?->name ?? ($incident->item_description ?: '-');
        $location = $incident->store?->name ?? '-';

        $title = 'Incident Updated';
        $message = "Incident <b>{$incident->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been updated by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('edit_incident', $incident->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $incident->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'edit_incident',
            $title,
            $message,
            'incidents',
            $incident->id
        );

        return redirect()->route('incidents.index')->with('success', 'Incident updated successfully.');
    }
}
