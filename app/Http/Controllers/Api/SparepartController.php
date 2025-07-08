<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sparepart;

class SparepartController extends Controller
{
    public function getStock($id)
    {
        $sparepart = Sparepart::find($id);
        if (!$sparepart) {
            return response()->json(['stock' => 0], 404);
        }
        return response()->json(['stock' => $sparepart->qty]);
    }
}
