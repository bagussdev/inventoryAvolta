<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    protected $table = 'incidents';

    protected $fillable = [
        'unique_id',        // Pelapor
        'pic_user',        // PIC pelapor
        'department_to',
        'status',
        'location',        // store_id
        'item_problem',    // items.id
        'attachment_staff',
        'message_user',
        'message_staff',
        'attachment_user',
        'confirmby',
        'pic_staff',
        'resolvedby',
        'resolved_at',
        'item_description'
    ];

    // Relasi ke equipment
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'item_problem');
    }

    // Relasi pelapor (user_id)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user');
    }

    // Relasi ke PIC user (pic_staff)
    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_staff');
    }

    // Relasi ke user yang mengonfirmasi (confirm_by)
    public function confirm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmby');
    }
    public function resolve(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolvedby');
    }

    // Relasi ke store melalui kolom location
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'location');
    }

    public function getItemAttribute()
    {
        return $this->equipment?->item;
    }
    public function usedSpareParts()
    {
        return $this->hasMany(UsedSparepart::class, 'incident_id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_to');
    }
}
