<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    /**
     * Data transaksi yang sudah difilter dari controller.
     * @var \Illuminate\Support\Collection
     */
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * Mengambil koleksi data yang akan diekspor.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Map data Eloquent model menjadi format yang siap diekspor ke Excel.
        // Pastikan kolom yang diambil sesuai dengan headings di bawah.
        return $this->transactions->map(function ($transaction) {
            return [
                'Transaction ID' => $transaction->id,
                'Category'       => ucfirst($transaction->type),
                'Item'           => $transaction->item->name ?? '-',
                'QTY'            => $transaction->qty,
                'S/N'            => $transaction->serial_number ?? '-',
                'Supplier'       => $transaction->supplier,
                'Date'           => $transaction->created_at->format('d M Y'),
                'Created By'     => $transaction->user->name,
            ];
        });
    }

    /**
     * Menambahkan heading (judul kolom) di baris pertama Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Transaction ID',
            'Category',
            'Item',
            'QTY',
            'S/N',
            'Supplier',
            'Date',
            'Created By',
        ];
    }
}
