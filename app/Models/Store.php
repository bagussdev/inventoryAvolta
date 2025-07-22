<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'store';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'site_code',
        'since',
        'location',
        'status',
        'type'
    ];

    protected $casts = [
        'since' => 'date',
    ];
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_location', 'id_store');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'store_location', 'id');
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'location', 'id');
    }
}
