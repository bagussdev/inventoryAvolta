<?php

namespace App\Exports;

use App\Models\Incident;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IncidentsCompletedExport implements FromCollection, WithHeadings, WithMapping
{
    protected $incidents;
    protected $index = 0;

    public function __construct($incidents)
    {
        $this->incidents = $incidents;
    }

    public function collection()
    {
        return $this->incidents;
    }

    public function map($incident): array
    {
        $this->index++;

        // Format item problem seperti di blade
        $itemName = optional(optional($incident->equipment)->item)->name;
        $alias = optional($incident->equipment)->alias;
        $description = $incident->item_description;

        $itemProblem = '-';
        if ($itemName) {
            $itemProblem = $itemName;
            if ($alias || $description) {
                $itemProblem .= ' - ' . ($alias ?? $description);
            }
        } elseif ($alias || $description) {
            $itemProblem = $alias ?? $description;
        }

        return [
            $this->index,
            $incident->unique_id ?? '-',
            $incident->user->name ?? '-',
            $incident->department->name ?? '-',
            ucwords(strtolower($itemProblem)),
            $incident->store->site_code ?? '-',
            Carbon::parse($incident->created_at)->format('d M Y'),
            Carbon::parse($incident->resolved_at)->format('d M Y'),
            $incident->picUser->name ?? '-',
            ucfirst($incident->status),
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Incident ID',
            'Reported By',
            'Report To',
            'Item Problem',
            'Location',
            'Start Date',
            'Resolve Date',
            'PIC Staff',
            'Status',
        ];
    }
}
