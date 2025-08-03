<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Maintenance;

class MaintenancesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $maintenances;
    protected $index = 0; // Untuk No urut

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
        $this->index++; // Increment setiap baris

        return [
            $this->index, // No
            'MNT' . str_pad($maintenance->id, 5, '0', STR_PAD_LEFT), // Maintenance ID
            $maintenance->equipment->item->name ?? '-',
            $maintenance->equipment->item->model ?? '-',
            $maintenance->equipment->item->brand ?? '-',
            $maintenance->equipment->store->name ?? '-',
            \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y'),
            ucfirst($maintenance->frequensi),
            optional($maintenance->staff)->name ?? '-',
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
            'Maintenance Date',
            'Frequency',
            'PIC Staff',
            'Status',
        ];
    }
}
