<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserDataController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('managementusermenu');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 5); // default 5

        $query = User::with(['role', 'department', 'location'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('no_telfon', 'like', "%{$search}%");
                });
            });

        if ($perPage === 'all') {
            $users = $query->orderBy('updated_at', 'desc')->get();
        } else {
            $users = $query->orderBy('updated_at', 'desc')->paginate((int) $perPage)
                ->appends([
                    'search' => $search,
                    'per_page' => $perPage,
                ]);
        }

        $perPageOptions = [5, 10, 20];

        return view('managementUser.index', compact('users', 'search', 'perPage', 'perPageOptions'));
    }
    public function tbody(Request $request)
    {
        $this->authorize('managementusermenu');

        $search = $request->input('search');
        $perPage = $request->input('per_page', 5);

        $query = User::with(['role', 'department', 'location'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('no_telfon', 'like', "%{$search}%");
                });
            });

        $users = $perPage === 'all'
            ? $query->orderByDesc('updated_at')->get()
            : $query->orderByDesc('updated_at')->paginate((int)$perPage)->appends($request->query());

        return view('partials.users-tbody', compact('users'))->render();
    }

    public function lastUpdated()
    {
        $this->authorize('managementusermenu');

        $lastUpdated = User::max('updated_at');

        return response()->json(['last_updated' => $lastUpdated]);
    }



    public function activate($id)
    {
        $this->authorize('usercontrol');
        $user = User::findOrFail($id);
        $user->status = 'Y';
        $user->save();

        return redirect()->route('users.index')->with('success', 'User activated successfully.');
    }

    public function deactivate($id)
    {
        $this->authorize('usercontrol');
        $user = User::findOrFail($id);
        $user->status = 'N';
        $user->save();

        return redirect()->route('users.index')->with('success', 'User deactivated successfully.');
    }

    public function create()
    {
        $this->authorize('managementuser.create');
        $roles = Role::all();
        $departments = Department::all();
        $stores = Store::all();
        return view('managementUser.create', compact('roles', 'departments', 'stores'));
    }
    public function store(Request $request)
    {
        $this->authorize('managementuser.create');
        $role = Role::findOrFail($request->role_id);

        // Validasi dasar
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'no_telfon' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'department_id' => $role->name === 'master' ? 'nullable' : 'required|exists:departments,id',
        ]);
        // Simpan user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_telfon' => $request->no_telfon,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
            'store_location' => $request->store_location ?? null,
            'status' => 'Y',
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }
    public function edit($id)
    {
        $this->authorize('managementuser.edit');
        $user = User::findOrFail($id);
        $roles = Role::all();
        $stores = Store::all();
        $departments = Department::all();

        return view('managementUser.edit', compact('user', 'roles', 'departments', 'stores'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('managementuser.edit');
        $user = User::findOrFail($id);
        $role = Role::findOrFail($request->role_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'no_telfon' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'store_location' => 'nullable|exists:store,id',
            'department_id' => $role->name === 'master' ? 'nullable' : 'required|exists:departments,id',
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->no_telfon = $request->no_telfon;
        $user->role_id = $request->role_id;
        $user->department_id = $request->department_id;
        $user->store_location = $request->store_location;

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        //
    }
}
