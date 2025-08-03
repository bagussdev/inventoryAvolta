<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Maintenance;
use Carbon\Carbon;

class MaintenancesCompletedExport implements FromCollection, WithHeadings, WithMapping
{
    protected $maintenances;
    protected $index = 0;

    public function __construct($maintenances)
    {
        $this->maintenances = $maintenances;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->maintenances;
    }

    /**
     * @var Maintenance $maintenance
     */
    public function map($maintenance): array
    {
        $this->index++;
        return [
            $this->index,
            'MNT' . str_pad($maintenance->id, 5, '0', STR_PAD_LEFT),
            $maintenance->equipment->item->name ?? '-',
            $maintenance->equipment->item->model ?? '-',
            $maintenance->equipment->item->brand ?? '-',
            $maintenance->equipment->store->name ?? '-',
            ucfirst($maintenance->frequensi),
            Carbon::parse($maintenance->maintenance_date)->format('d M Y'),
            Carbon::parse($maintenance->resolved_at)->format('d M Y'),
            $maintenance->staff->name ?? '-', // PIC Staff
            ucfirst($maintenance->status),
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Maintenance ID',
            'Equipment',
            'Model',
            'Brand',
            'Location',
            'Frequency',
            'Maintenance Date',
            'Resolved At',
            'PIC Staff',
            'Status',
        ];
    }
}
