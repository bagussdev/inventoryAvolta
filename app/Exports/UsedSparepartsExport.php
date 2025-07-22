<?php

namespace App\Exports;

use App\Models\UsedSparepart;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsedSparepartsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UsedSparepart::all();
    }
}
