<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Incident;
use Carbon\Carbon;

class IncidentsCompletedExport implements FromCollection, WithHeadings, WithMapping
{
    protected $incidents;

    public function __construct($incidents)
    {
        $this->incidents = $incidents;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->incidents;
    }

    /**
     * @var Incident $incident
     */
    public function map($incident): array
    {
        return [
            $incident->id,
            $incident->equipment->item->name ?? '-',
            $incident->equipment->item->model ?? '-',
            $incident->equipment->item->brand ?? '-',
            $incident->equipment->store->name ?? '-',
            $incident->user->name ?? '-',
            Carbon::parse($incident->created_at)->format('d M Y'),
            ucfirst($incident->status),
            $incident->notes ?? '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Incident Id',
            'Equipment',
            'Model',
            'Brand',
            'Location',
            'Reported By',
            'Reported Date',
            'Status',
            'Notes',
        ];
    }
}
