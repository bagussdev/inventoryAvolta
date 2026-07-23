<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintenance;
use Carbon\Carbon;
use App\Models\NotificationPreference;
use App\Services\NotificationService;

class CheckMaintenanceStatus extends Command
{
    protected $signature = 'check:maintenance-status';
    protected $description = 'Update maintenance status to maintenance if H-1';

    private function getNotificationTargets(string $type, int $departmentId = null): array
    {
        $preferences = NotificationPreference::where('type', $type)->pluck('role_id')->toArray();

        $targets = [];

        foreach ($preferences as $roleId) {
            if ($roleId == 1) { // Master (tanpa department)
                $targets[] = ['role_id' => $roleId];
            } elseif ($departmentId) {
                $targets[] = ['role_id' => $roleId, 'department_id' => $departmentId];
            }
        }

        return $targets;
    }

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $maintenances = Maintenance::with(['equipment.item', 'equipment.store'])
            ->where('status', 'not due')
            ->whereDate('maintenance_date', '<=', $tomorrow)
            ->get();

        $updatedCount = 0;

        foreach ($maintenances as $maintenance) {
            $maintenance->update(['status' => 'maintenance']);
            $updatedCount++;

            $equipment = $maintenance->equipment;
            $item = $equipment?->item;
            $location = $equipment?->store?->name ?? '-';
            $itemName = $item?->name ?? '-';
            $departmentId = $item?->department_id;

            $maintenanceDate = Carbon::parse($maintenance->maintenance_date)->format('d M Y');

            $targets = $this->getNotificationTargets('maintenance_schedule', $departmentId);

            if (!empty($targets)) {
                NotificationService::send(
                    $targets,
                    'update maintenance',
                    'Scheduled Maintenance Incoming',
                    "Scheduled maintenance for <b>{$itemName}</b> at <b>{$location}</b> is due on <b>{$maintenanceDate}</b>.",
                    'maintenances',
                    $maintenance->id
                );
            }
        }

        if ($updatedCount > 0) {
            $this->info("Updated {$updatedCount} maintenance(s) to status 'maintenance' and notifications sent.");
        }
    }
}
