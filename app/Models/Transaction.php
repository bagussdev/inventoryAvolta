<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'items_id',
        'type',
        'serial_number',
        'qty',
        'supplier',
        'photoitems',
        'attachmentfile',
        'notes',
        'users_id'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'items_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
