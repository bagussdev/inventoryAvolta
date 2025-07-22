<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\NotificationPreference;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NotificationPreferenceController extends Controller
{
    use AuthorizesRequests;
    protected $types = [
        'create_item' => 'Create Item',
        'edit_item' => 'Edit Item',
        'import_item' => 'Import Excel Item',
        'deleted_item' => 'Deleted Item',
        'restore_item' => 'Restore Deleted Item',
        'equipment_migrate' => 'Migrate Equipment',
        'equiment_create' => 'Create Equipment',
        'sparepart_create' => 'Create Sparepart',
        'maintenance_schedule' => 'Maintenance Schedule',
        'edit_maintenance' => 'Edit Schedule Maintenance',
        'proses_maintenance' => 'Proses Maintenance',
        'confirm_maintenance' => 'Resolve Maintenance',
        'closed_maintenance' => 'Closed Maintenance',
        'update_spareparts' => 'Update Maintenance Spareparts',
        'create_incident' => 'Create Incident',
        'edit_incident' => 'Edit Incident',
        'start_incident' => 'Proses Incident',
        'restart_incident' => 'Restart Incident',
        'pending_incident' => 'Pending Incident',
        'resolve_incident' => 'Resolve Incident',
        'update_sparepart_incident' => 'Update Incident Spareparts',
        'close_incident' => 'Closed Incident',
        'create_request' => 'Create Request',
        'edit_request' => 'Edit Request',
        'start_request' => 'Proses Request',
        'restart_request' => 'Restart Request',
        'pending_request' => 'Pending Request',
        'resolve_request' => 'Completed Request',
        'update_sparepart_request' => 'Update Request Spareparts'
        // Tambahkan jenis notifikasi lainnya di sini
    ];

    public function index()
    {
        $this->authorize('notifpermission');
        $roles = Role::all();

        $savedPreferences = NotificationPreference::all()
            ->groupBy('type')
            ->map(function ($items) {
                return $items->pluck('role_id')->toArray();
            });

        return view('notification_preferences.index', [
            'roles' => $roles,
            'notificationTypes' => $this->types,
            'savedPreferences' => $savedPreferences
        ]);
    }

    public function save(Request $request)
    {
        $this->authorize('notifpermission');
        $data = $request->input('preferences', []);

        // Hapus semua preferences dulu (satu kali clear)
        NotificationPreference::truncate();

        foreach ($data as $type => $roleIds) {
            foreach ($roleIds as $roleId) {
                NotificationPreference::create([
                    'type' => $type,
                    'role_id' => $roleId,
                ]);
            }
        }

        return redirect()->route('notification-preferences.index')->with('success', 'Preferences updated.');
    }
}
