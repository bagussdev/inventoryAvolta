<?php

namespace App\Exports;

use App\Models\Laptop;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaptopsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $statusLabels = [
            'available' => 'Available',
            'in_use'    => 'In Use',
            'broken'    => 'Broken',
            'scrap'     => 'Scrap',
        ];

        $rows = Laptop::with(['store', 'creator'])
            ->orderByDesc('hostname')
            ->get();

        $counter = 0;

        return $rows->map(function ($laptop) use ($statusLabels, &$counter) {
            $counter++;
            return [
                'No'           => $counter,
                'Hostname'     => $laptop->hostname,
                'SerialNumber' => $laptop->serialnumber,
                'Model'        => $laptop->model,
                'Brand'        => $laptop->brand,
                'User'         => $laptop->user,
                'Location'     => $laptop->store->name ?? '-',
                'TypeWindows'  => $laptop->typewindows,
                'OSStatus'     => $laptop->osstatus,
                'IP'           => $laptop->iprealvnc,
                'Status'       => $statusLabels[$laptop->status] ?? $laptop->status,
                'Created By'   => $laptop->creator->name ?? '-',
                'Created At'   => optional($laptop->created_at)->format('Y-m-d H:i:s'),
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
