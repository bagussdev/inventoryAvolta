<?php

namespace App\Exports;

use App\Models\UsedSparepart;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsedSparepartsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return UsedSparepart::with(['sparepart.item', 'maintenance.staff', 'incident.picUser', 'request.picUser'])->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Item',
            'Qty',
            'Type',
            'Reference',
            'PIC',
            'Note',
        ];
    }

    public function map($used): array
    {
        static $i = 0;
        $i++;

        $type = $used->maintenance_id ? 'Maintenance' : ($used->incident_id ? 'Incident' : 'Request');

        $reference = '-';
        if ($used->maintenance_id) {
            $reference = 'MNT0000' . $used->maintenance_id;
        } elseif ($used->incident_id) {
            $reference = $used->incident?->unique_id ?? '-';
        } elseif ($used->request_id) {
            $reference = $used->request?->unique_id ?? '-';
        }

        $pic = '-';
        if ($used->maintenance_id) {
            $pic = $used->maintenance?->staff?->name ?? '-';
        } elseif ($used->incident_id) {
            $pic = $used->incident?->picUser?->name ?? '-';
        } elseif ($used->request_id) {
            $pic = $used->request?->picUser?->name ?? '-';
        }

        return [
            $i,
            $used->created_at->format('d M Y'),
            ucfirst(strtolower($used->sparepart->item->name ?? '-')) . ' - ' . ($used->sparepart->item->model ?? '-'),
            '-' . $used->qty,
            $type,
            $reference,
            $pic,
            $used->note ?? '-',
        ];
    }
}
