<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Department;
use App\Models\RolePermission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('permissionsettingsmenu');
        $roles = Role::with('permissions')->get();

        // Ambil semua permission
        $permissions = Permission::all();

        return view('permissions.index', compact('roles', 'permissions'));
    }
    public function update(Request $request, $id)
    {
        $this->authorize('permissionsettings.edit');
        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'required|string' // JSON string dari TomSelect
        ]);

        $permissionNames = json_decode($request->permissions, true);

        $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');

        $role->permissions()->sync($permissionIds);

        return redirect()->route('permissions.index')->with('success', 'Permissions updated successfully.');
    }




    public function store(Request $request)
    {
        $this->authorize('permissionsettings.edit');
        $data = $request->input('permissions');
        RolePermission::truncate();

        foreach ($data as $role_id => $perms) {
            foreach ($perms as $permission) {
                RolePermission::create([
                    'role_id' => $role_id,
                    'permission_id' => $permission,
                ]);
            }
        }

        return back()->with('success', 'Permissions updated successfully.');
    }
}
