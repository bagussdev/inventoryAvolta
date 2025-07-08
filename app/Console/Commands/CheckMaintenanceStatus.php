<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintenance;
use Carbon\Carbon;

class CheckMaintenanceStatus extends Command
{
    protected $signature = 'check:maintenance-status';
    protected $description = 'Update maintenance status to maintenance if H-1';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $updated = Maintenance::where('status', 'not due')
            ->whereDate('maintenance_date', $tomorrow)
            ->update(['status' => 'maintenance']);

        $this->info("Updated $updated maintenance(s) to status 'maintenance'");
    }
}
