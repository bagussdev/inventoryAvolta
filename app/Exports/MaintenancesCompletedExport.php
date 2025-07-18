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
        return [
            $maintenance->id,
            $maintenance->equipment->item->name ?? '-',
            $maintenance->equipment->item->model ?? '-',
            $maintenance->equipment->item->brand ?? '-',
            $maintenance->equipment->store->name ?? '-',
            ucfirst($maintenance->frequensi),
            Carbon::parse($maintenance->maintenance_date)->format('d M Y'),
            ucfirst($maintenance->status),
            Carbon::parse($maintenance->updated_at)->format('d M Y'), // Resolved At
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Maintenance Id',
            'Equipment',
            'Model',
            'Brand',
            'Location',
            'Frequency',
            'Maintenance Date',
            'Status',
            'Resolved At',
        ];
    }
}
