<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FullPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            'inventoryitemsmenu',
            'equipmentsmenu',
            'sparepartsmenu',
            'historytransactionsmenu',
            'usedsparepartsmenu',
            'schedulemaintenancemenu',
            'completedmaintenancemenu',
            'incidentmenu',
            'completedincidentmenu',
            'requestmenu',
            'completedrequestmenu',
            'outletlistmenu',
            'managementusermenu',
            'permissionsettingsmenu',
        ];

        $actions = ['create', 'edit', 'delete'];
        $now = now();

        foreach ($menus as $menu) {
            $base = str_replace('menu', '', $menu);
            $label = ucfirst(str_replace(['menu', '_'], ['', ' '], $menu));

            // Menu utama
            DB::table('permissions')->insert([
                'name' => $menu,
                'group' => $menu,
                'label' => $label . ' Menu',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Sub permission
            foreach ($actions as $action) {
                DB::table('permissions')->insert([
                    'name' => $base . '.' . $action,
                    'group' => $menu,
                    'label' => ucfirst($action) . ' ' . $label,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
