<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'type', 'brand', 'model', 'category', 'department_id'];

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'items_id');
    }
    public function sparepart()
    {
        return $this->hasMany(Sparepart::class, 'items_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
