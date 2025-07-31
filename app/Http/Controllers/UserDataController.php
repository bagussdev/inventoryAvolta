<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Mail\UserRegisteredMail;

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

        // Tidak boleh mengaktifkan diri sendiri
        if (Auth::user()->id == $id) {
            return redirect()->route('users.index')->with('error', 'Kamu tidak bisa mengaktifkan akun sendiri.');
        }

        $user = User::findOrFail($id);

        if ($user->status === 'Y') {
            return redirect()->route('users.index')->with('info', 'User sudah aktif.');
        }

        $user->status = 'Y';
        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diaktifkan.');
    }

    public function deactivate($id)
    {
        $this->authorize('usercontrol');

        // Tidak boleh menonaktifkan diri sendiri
        if (Auth::user()->id == $id) {
            return redirect()->route('users.index')->with('error', 'Kamu tidak bisa menonaktifkan akun sendiri.');
        }

        $user = User::findOrFail($id);

        if ($user->status === 'N') {
            return redirect()->route('users.index')->with('info', 'User sudah nonaktif.');
        }

        $user->status = 'N';
        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil dinonaktifkan.');
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

        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'no_telfon' => 'nullable|numeric|digits_between:1,12',
            'role_id' => 'required|exists:roles,id',
            'department_id' => $role->name === 'master' ? 'nullable' : 'required|exists:departments,id',
        ]);

        // Generate password acak
        $plainPassword = Str::random(10);

        // Simpan user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'no_telfon' => $validated['no_telfon'],
            'password' => Hash::make($plainPassword),
            'role_id' => $validated['role_id'],
            'department_id' => $validated['department_id'],
            'store_location' => $request->store_location ?? null,
            'status' => 'Y',
        ]);

        // Kirim email ke user
        try {
            Mail::to($validated['email'])->send(new UserRegisteredMail($user, $plainPassword));
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'User ditambahkan, tapi email gagal dikirim: ' . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan dan email telah dikirim.');
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
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Update atribut user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'no_telfon' => $validated['no_telfon'],
            'role_id' => $validated['role_id'],
            'department_id' => $validated['department_id'],
            'store_location' => $validated['store_location'],
        ]);

        // Update password jika diisi
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $this->authorize('managementuser.delete');

        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Kamu tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
