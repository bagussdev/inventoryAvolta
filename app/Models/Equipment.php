<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipments';

    protected $fillable = [
        'items_id',
        'serial_number',
        'transactions_id',
        'supplier',
        'photo',
        'location',
        'status',
        'notes',
        'alias'
    ];

    // Relasi ke Item
    public function item()
    {
        return $this->belongsTo(Item::class, 'items_id');
    }

    // Relasi ke Transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactions_id');
    }

    // Relasi ke Store (jika location adalah id dari tabel store)
    public function store()
    {
        return $this->belongsTo(Store::class, 'location');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    // app/Models/Equipment.php

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'item_problem');
    }
}
