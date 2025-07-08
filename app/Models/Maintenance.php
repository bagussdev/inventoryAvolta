<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = [
        'equipment_id',
        'frequensi',
        'maintenance_date',
        'picstaff',
        'status',
        'attachment',
        'notes',
        'confirmBy',
        'resolved_at'
    ];

    // Relasi ke Equipment
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    // Relasi ke PIC Staff
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picstaff');
    }

    // Relasi ke Supervisor yang mengonfirmasi
    public function confirm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmby');
    }
    public function usedSpareParts()
    {
        return $this->hasMany(UsedSparepart::class, 'maintenance_id');
    }
}
