<?php

namespace App\Exports;

use App\Models\Desktop;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DesktopsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $statusLabels = [
            'available' => 'Available',
            'in_use'    => 'In Use',
            'broken'    => 'Broken',
            'scrap'     => 'Scrap',
        ];

        $rows = Desktop::with(['store', 'creator'])
            ->orderByDesc('hostname')
            ->get();

        $counter = 0;

        return $rows->map(function ($desktop) use ($statusLabels, &$counter) {
            $counter++;
            return [
                'No'           => $counter,
                'Hostname'     => $desktop->hostname,
                'SerialNumber' => $desktop->serialnumber,
                'Model'        => $desktop->model,
                'Brand'        => $desktop->brand,
                'User'         => $desktop->user,
                'Location'     => $desktop->store->name ?? '-',
                'TypeWindows'  => $desktop->typewindows,
                'OSStatus'     => $desktop->osstatus,
                'IP'           => $desktop->iprealvnc,
                'Status'       => $statusLabels[$desktop->status] ?? $desktop->status,
                'Created By'   => $desktop->creator->name ?? '-',
                'Created At'   => optional($desktop->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Hostname',
            'SerialNumber',
            'Model',
            'Brand',
            'User',
            'Location',
            'TypeWindows',
            'OSStatus',
            'IP',
            'Status',
            'Created By',
            'Created At',
        ];
    }
}
