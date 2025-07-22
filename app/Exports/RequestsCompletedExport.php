<?php

namespace App\Exports;

use App\Models\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RequestsCompletedExport implements FromCollection, WithHeadings, WithMapping
{
    protected $requests;

    public function __construct($requests = null)
    {
        $this->requests = $requests ?? Request::where('status', 'completed')->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->requests;
    }

    /**
     * @var Request $request
     */
    public function map($request): array
    {
        return [
            $request->id,
            $request->unique_id,
            $request->user->name ?? '-',
            $request->store->name ?? '-',
            $request->item_request ?? '-',
            $request->qty ?? '-',
            ucfirst($request->status),
            Carbon::parse($request->created_at)->format('d M Y'),
            $request->message_user ?? '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Request ID',
            'Reported By',
            'Location',
            'Item',
            'Qty',
            'Status',
            'Date',
            'Message',
        ];
    }
}
