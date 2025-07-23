<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Department;
use App\Models\Sparepart;
use App\Models\UsedSparepart;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RequestsExport;
use App\Exports\RequestsCompletedExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\NotificationPreference;
use App\Services\NotificationService;


class RequestController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = RequestModel::with(['user', 'store'])->oldest('created_at')
            ->whereNotIn('status', ['completed']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('item_request', 'like', "%{$search}%")
                    ->orWhere('qty', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('site_code', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                // User biasa hanya bisa lihat request dari lokasi store mereka
                if ($user->store_location) {
                    $query->where('location', $user->store_location);
                } else {
                    $query->whereRaw('1=0'); // Tidak bisa akses data apa pun
                }
            } else {
                // Role 2,3,4 (manager, spv, staff) lihat berdasarkan departemen
                if ($user->department_id) {
                    $query->where('department_to', $user->department_id);
                } else {
                    $query->whereRaw('1=0');
                }
            }
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        }

        if ($perPage === 'all') {
            $requests = $query->orderByDesc('created_at')->get();
        } else {
            $requests = $query->orderByDesc('created_at')->paginate((int) $perPage)->appends($request->query());
        }
        return view('requests.index', compact('requests', 'perPage', 'search', 'startDate', 'endDate'));
    }
    public function tbody(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = RequestModel::with(['user', 'store'])->oldest('created_at')->whereNotIn('status', ['completed']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('item_request', 'like', "%{$search}%")
                    ->orWhere('qty', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('site_code', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                // User biasa hanya bisa lihat request dari lokasi store mereka
                if ($user->store_location) {
                    $query->where('location', $user->store_location);
                } else {
                    $query->whereRaw('1=0'); // Tidak bisa akses data apa pun
                }
            } else {
                // Role 2,3,4 (manager, spv, staff) lihat berdasarkan departemen
                if ($user->department_id) {
                    $query->where('department_to', $user->department_id);
                } else {
                    $query->whereRaw('1=0');
                }
            }
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        }

        $requests = $perPage === 'all'
            ? $query->orderByDesc('created_at')->get()
            : $query->orderByDesc('created_at')->paginate((int) $perPage);

        return view('partials.requests-tbody', compact('requests'));
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

    public function lastUpdated()
    {
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = RequestModel::query();

        if (!$isMaster && $user->store_location) {
            $query->where('location', $user->store_location);
        }

        $lastUpdated = $query->max('updated_at');

        return response()->json(['last_updated' => $lastUpdated]);
    }

    public function completed(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = RequestModel::with(['user', 'store'])->where('status', 'completed');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('item_request', 'like', "%{$search}%")
                    ->orWhere('qty', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('site_code', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster) {
            if ($user->role_id === 5 || strtolower($user->role->name) === 'user') {
                // User biasa hanya bisa lihat request dari lokasi store mereka
                if ($user->store_location) {
                    $query->where('location', $user->store_location);
                } else {
                    $query->whereRaw('1=0'); // Tidak bisa akses data apa pun
                }
            } else {
                // Role 2,3,4 (manager, spv, staff) lihat berdasarkan departemen
                if ($user->department_id) {
                    $query->where('department_to', $user->department_id);
                } else {
                    $query->whereRaw('1=0');
                }
            }
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        }

        if ($perPage === 'all') {
            $requests = $query->orderByDesc('created_at')->get();
        } else {
            $requests = $query->orderByDesc('created_at')->paginate((int) $perPage)->appends($request->query());
        }
        return view('requests.completed', compact('requests', 'perPage', 'search', 'startDate', 'endDate'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = RequestModel::with(['user', 'store'])->whereNotIn('status', ['completed']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('item_request', 'like', "%{$search}%")
                    ->orWhere('qty', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster && $user->store_location) {
            $query->where('store_id', $user->store_location);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        }

        $filteredRequests = $query->latest('created_at')->get();

        $filename = 'requests_filtered_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new RequestsExport($filteredRequests), $filename);
    }
    public function exportCompleted(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $query = RequestModel::with(['user', 'store'])->whereIn('status', ['completed']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                    ->orWhere('item_request', 'like', "%{$search}%")
                    ->orWhere('qty', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!$isMaster && $user->store_location) {
            $query->where('store_id', $user->store_location);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        }

        $filteredRequests = $query->latest('created_at')->get();

        $filename = 'requestsCompleted_filtered_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new RequestsCompletedExport($filteredRequests), $filename);
    }
    public function create()
    {
        $user = Auth::user();
        $stores = Store::where('type', 'Store')->get();
        $deptItId = Department::where('name', 'IT')->value('id');
        $deptMepId = Department::where('name', 'MEP')->value('id');

        return view('requests.create', compact('stores', 'deptItId', 'deptMepId'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id'        => 'required|exists:store,id',
            'department_to'   => 'required|exists:departments,id',
            'item_request'    => 'required|string|max:255',
            'qty'             => 'required|integer|min:1',
            'message_user'    => 'required|string|max:1000',
            'attachment_user' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480',
        ]);

        try {
            DB::beginTransaction();

            // Lock row terakhir untuk hindari duplikasi ID
            $last = DB::table('requests')
                ->select('unique_id')
                ->where('unique_id', 'like', 'REQ%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $lastNum = $last ? (int) Str::after($last->unique_id, 'REQ') : 0;
            $newUniqueId = 'REQ' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

            // Upload file jika ada
            $attachmentPath = null;
            if ($request->hasFile('attachment_user')) {
                $attachmentPath = $request->file('attachment_user')->store('request_attachments', 'public');
            }

            $requestModel = RequestModel::create([
                'unique_id'       => $newUniqueId,
                'location'        => $validated['store_id'],
                'department_to'   => $validated['department_to'],
                'item_request'    => $validated['item_request'],
                'qty'             => $validated['qty'],
                'message_user'    => $validated['message_user'],
                'attachment_user' => $attachmentPath,
                'pic_user'        => Auth::id(),
                'status'          => 'waiting',
            ]);

            $user = Auth::user();
            $storeName = $requestModel->store?->name ?? '-';
            $item = $requestModel->item_request;

            $title = 'New Request Created';
            $message = "New request <b>{$requestModel->unique_id}</b> has been submitted by <b>{$user->name}</b> for item <b>{$item}</b> at <b>{$storeName}</b>.";

            $targets = $this->getNotificationTargets('create_request', $requestModel->department_to);
            foreach ($targets as &$target) {
                $target['store_id'] = $requestModel->store->id;
            }
            unset($target);
            NotificationService::send(
                $targets,
                'create_request',
                $title,
                $message,
                'requests',
                $requestModel->id
            );

            DB::commit();

            return redirect()->route('requests.index')->with('success', 'Request created successfully.');
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

    public function start($id)
    {
        $request = RequestModel::findOrFail($id);
        $this->authorize('request.proses', $request);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if ($request->status !== 'waiting') {
            abort(403, 'Request is not in a startable state.');
        }

        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $request->department_to) {
                abort(403, 'You can only start requests assigned to your department.');
            }
        }

        $request->update([
            'status' => 'in progress',
            'pic_staff' => Auth::id()
        ]);

        $item = $request->item_request;
        $location = $request->store?->name ?? '-';

        $title = 'Request In Progress';
        $message = "Request <b>{$request->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> is now being handled by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('start_request', $request->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $request->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'start_request',
            $title,
            $message,
            'requests',
            $request->id
        );

        return redirect()->route('requests.index')->with('success', 'request marked as In Progress.');
    }
    public function restart($id)
    {
        $request = RequestModel::findOrFail($id);
        $this->authorize('request.proses', $request);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Validasi: hanya bisa restart jika status saat ini 'pending'
        if ($request->status !== 'pending') {
            abort(403, 'Only requests with pending status can be restarted.');
        }

        // Validasi: selain master, hanya boleh restart jika cocok departemen
        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $request->department_to) {
                abort(403, 'You can only restart requests assigned to your department.');
            }
        }

        $request->update([
            'status' => 'in progress',
            'pic_staff' => $user->id
        ]);

        $item = $request->item_request;
        $location = $request->store?->name ?? '-';

        $title = 'Request Restarted';
        $message = "Request <b>{$request->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been restarted by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('restart_request', $request->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $request->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'restart_request',
            $title,
            $message,
            'requests',
            $request->id
        );

        return redirect()->route('requests.index')->with('success', 'request marked as In Progress.');
    }

    public function edit($id)
    {
        $this->authorize('request.edit');

        $request = RequestModel::with(['store', 'department'])->findOrFail($id);
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Hanya bisa diedit jika status masih 'waiting'
        if ($request->status !== 'waiting') {
            abort(403, 'Only requests with waiting status can be edited.');
        }

        // Filter berdasarkan role dan department
        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $request->department_to) {
                abort(403, 'You are only allowed to edit requests from your department.');
            }
        }

        // User biasa (role_id = 5) hanya bisa edit milik sendiri
        if ($user->role_id === 5 && $user->id !== $request->pic_user) {
            abort(403, 'You are only allowed to edit your own request.');
        }

        return view('requests.edit', compact('request'));
    }


    public function update(Request $request, $id)
    {
        $this->authorize('request.edit');
        $requestModel = RequestModel::findOrFail($id);

        $validated = $request->validate([
            'item_request' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'message_user' => 'required|string|max:1000',
            'attachment_user' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480',
        ]);

        // Handle file jika ada upload baru
        if ($request->hasFile('attachment_user')) {
            // Hapus file lama jika ada
            if ($requestModel->attachment_user && Storage::disk('public')->exists($requestModel->attachment_user)) {
                Storage::disk('public')->delete($requestModel->attachment_user);
            }

            $path = $request->file('attachment_user')->store('request_attachments', 'public');
            $requestModel->attachment_user = $path;
        }

        $requestModel->update([
            'item_request' => $validated['item_request'],
            'qty' => $validated['qty'],
            'message_user' => $validated['message_user'],
        ]);

        $user = Auth::user();
        $item = $requestModel->item_request;
        $location = $requestModel->store?->name ?? '-';

        $title = 'Request Updated';
        $message = "Request <b>{$requestModel->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been updated by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('edit_request', $requestModel->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $requestModel->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'edit_request',
            $title,
            $message,
            'requests',
            $requestModel->id
        );

        return redirect()->route('requests.index')->with('success', 'Request updated successfully.');
    }
    public function show(RequestModel $request)
    {
        $request->load([
            'user',                  // Reporter (user_id)
            'picUser',               // PIC Reporter (pic_user)
            'department',
            'store',                 // Store (location)
            'usedSpareParts.sparepart.item'
        ]);

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        // Role 2, 3, 4 (Manager, SPV, Staff)
        if (!$isMaster && in_array($user->role_id, [2, 3, 4])) {
            if ($user->department_id !== $request->department_to) {
                abort(403, 'You are only allowed to view requests from your department.');
            }
        }

        // Role 5 (User biasa) hanya bisa lihat milik sendiri
        if ($user->role_id === 5) {
            if ($user->store_location !== $request->location) {
                abort(403, 'You do not have permission to view this incident.');
            }
        }

        $spareparts = Sparepart::with('item')->get();

        return view('requests.show', compact('request', 'spareparts'));
    }

    public function updateSpareparts(Request $request, $id)
    {
        $this->authorize('request.update');
        $request->validate([
            'spareparts' => 'nullable|array',
            'spareparts.*.id' => 'required|exists:spareparts,id',
            'spareparts.*.qty' => 'required|integer|min:1',
            'spareparts.*.note' => 'nullable|string|max:255',
            'spareparts.*.used_id' => 'nullable|exists:used_spareparts,id',
            'deleted_ids' => 'nullable|array',
            'deleted_ids.*' => 'exists:used_spareparts,id',
        ]);

        $requestModel = RequestModel::with('usedSpareParts')->findOrFail($id);
        $oldUsedById = $requestModel->usedSpareParts->keyBy('id');
        $newInputs = collect($request->spareparts ?? []);
        $deletedIds = collect($request->deleted_ids ?? []);

        DB::beginTransaction();
        try {
            // Handle deletions
            foreach ($deletedIds as $deletedId) {
                $used = UsedSparepart::find($deletedId);
                if ($used && $used->request_id === $requestModel->id) {
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
                    $existingUsed = $requestModel->usedSpareParts()->where('spareparts_id', $newSparepartId)->first();

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
                            'request_id' => $requestModel->id,
                            'qty' => $newQty,
                            'note' => $note,
                        ]);
                    }
                }
            }

            $user = Auth::user();
            $item = $requestModel->item_request;
            $location = $requestModel->store?->name ?? '-';

            $title = 'Request Spareparts Updated';
            $message = "Spareparts for request <b>{$requestModel->unique_id}</b> (item <b>{$item}</b> at <b>{$location}</b>) have been updated by <b>{$user->name}</b>.";

            $targets = $this->getNotificationTargets('update_sparepart_request', $requestModel->department_to);
            foreach ($targets as &$target) {
                $target['store_id'] = $requestModel->store->id;
            }
            unset($target);
            NotificationService::send(
                $targets,
                'update_sparepart_request',
                $title,
                $message,
                'requests',
                $requestModel->id
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

    public function pending(Request $request, $id)
    {
        $this->authorize('request.pending');

        $requestModel = RequestModel::findOrFail($id);
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        if ($requestModel->status !== 'in progress') {
            abort(403, 'Only requests with status "in progress" can be marked as pending.');
        }

        if (!$isMaster) {
            if ($user->role_id === 5) {
                abort(403, 'You are not allowed to mark this request as pending.');
            } elseif (in_array($user->role_id, [2, 3, 4])) {
                if ($user->department_id !== $requestModel->department_to) {
                    abort(403, 'You are only allowed to mark requests from your department.');
                }
            } else {
                abort(403, 'Unauthorized access.');
            }
        }

        $requestModel->update([
            'status' => 'pending',
            'message_staff' => $request->notes,
        ]);

        $item = $requestModel->item_request;
        $location = $requestModel->store?->name ?? '-';

        $title = 'Request Marked as Pending';
        $message = "Request <b>{$requestModel->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been marked as <b>Pending</b> by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('pending_request', $requestModel->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $requestModel->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'pending_request',
            $title,
            $message,
            'requests',
            $requestModel->id
        );

        return redirect()->route('requests.index')->with('success', 'Request marked as Pending.');
    }

    public function resolve($id)
    {
        $this->authorize('request.resolve');

        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $request = RequestModel::with([
            'store',                      // Directly using 'store' relationship
            'user',                       // The 'user' who reported the incident
            'picUser',                    // The 'picUser' (PIC of the reporter)
            'department',
            'usedSpareParts.sparepart.item'
        ])->findOrFail($id);

        if ($request->status !== 'in progress') {
            abort(404);
        }

        $sparepartsQuery = Sparepart::where('qty', '>', 0)->with('item');

        if (!$isMaster && $user->department_id) {
            $sparepartsQuery->whereHas('item.department', function ($q) use ($user) {
                $q->where('id', $user->department_id);
            });
        }

        $spareparts = $sparepartsQuery->get();

        return view('requests.confirm', compact('request', 'spareparts'));
    }

    public function submitConfirm(Request $request, $id)
    {
        $this->authorize('request.proses');
        $user = Auth::user();
        $isMaster = Gate::allows('isMaster');

        $request->validate([
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:10240',
            'spareparts' => 'nullable|array',
            'spareparts.*.id' => 'nullable|exists:spareparts,id',
            'spareparts.*.qty' => 'nullable|integer|min:1',
            'spareparts.*.note' => 'nullable|string|max:255',
        ]);

        $requestModel = RequestModel::findOrFail($id);

        if ($request->status !== 'in progress') {
            abort(404, 'Request is not in progress.');
        }

        // Role-based access control
        if (!$isMaster) {
            if ($user->role_id === 5) {
                abort(403, 'You are not authorized to resolve this request.');
            } elseif (in_array($user->role_id, [2, 3, 4])) {
                if ($user->department_id !== $request->department_to) {
                    abort(403, 'You are only allowed to resolve requests from your department.');
                }
            } else {
                abort(403, 'Unauthorized access.');
            }
        }

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
            $file = $request->file('attachment')->store('request_attachments', 'public');
            $requestModel->attachment_staff = $file;
        }

        $requestModel->message_staff = $request->notes;
        $requestModel->status = 'completed';
        $requestModel->resolved_at = now();
        $requestModel->save();

        // Simpan UsedSpareparts yang sudah digabung
        foreach ($groupedSpareparts as $spare) {
            $sparepart = Sparepart::find($spare['id']);
            if (!$sparepart) continue;

            UsedSparepart::create([
                'spareparts_id' => $spare['id'],
                'request_id' => $requestModel->id,
                'qty' => $spare['qty'],
                'note' => $spare['note'] ?? null,
            ]);

            $sparepart->qty -= $spare['qty'];
            $sparepart->status = $sparepart->qty < 0
                ? 'empty'
                : ($sparepart->qty > 5 ? 'available' : 'low');
            $sparepart->save();
        }
        $item = $requestModel->item_request;
        $location = $requestModel->store?->name ?? '-';

        $title = 'Request Resolved';
        $message = "Request <b>{$requestModel->unique_id}</b> for item <b>{$item}</b> at <b>{$location}</b> has been <b>completed</b> by <b>{$user->name}</b>.";

        $targets = $this->getNotificationTargets('resolve_request', $requestModel->department_to);
        foreach ($targets as &$target) {
            $target['store_id'] = $requestModel->store->id;
        }
        unset($target);
        NotificationService::send(
            $targets,
            'resolve_request',
            $title,
            $message,
            'requests',
            $requestModel->id
        );

        return redirect()->route('requests.index')->with('success', 'Request resolved and confirmed successfully.');
    }
}
