<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $table = 'spareparts';
    protected $fillable = [
        'items_id',
        'qty',
        'transactions_id',
        'status'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'items_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactions_id');
    }

    public function usedSpareparts()
    {
        return $this->hasMany(UsedSparepart::class, 'spareparts_id');
    }
}
