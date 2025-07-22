<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = Permission::pluck('id', 'name');

        // Master: semua permission
        $master = Role::where('name', 'Master')->first();
        $master->permissions()->sync($allPermissions->values());

        // User: hanya beberapa
        $userPermissions = [
            'equipmentmenu',
            'incidentmenu',
            'requestmenu',
            'completedrequestmenu',
            'settingsmenu',
        ];
        $user = Role::where('name', 'User')->first();
        $user->permissions()->sync($allPermissions->only($userPermissions)->values());

        // SPV: semua kecuali managementuser & permissionsettings
        $excludedSpv = ['managementusermenu', 'permissionsettingsmenu'];
        $spvPermissions = $allPermissions->except($excludedSpv)->values();
        $spv = Role::where('name', 'SPV')->first();
        $spv->permissions()->sync($spvPermissions);
    }
}
