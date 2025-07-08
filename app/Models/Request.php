<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'unique_id',
        'pic_user',
        'department_to',
        'location',
        'item_request',
        'status',
        'message_user',
        'message_staff',
        'attachment_user',
        'attachment_staff',
        'pic_staff',
        'qty',
    ];

    // Relasi ke user pelapor
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user');
    }

    // Relasi ke store lokasi permintaan
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'location');
    }

    // Relasi ke staff yang menangani
    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_staff');
    }
    public function usedSpareParts()
    {
        return $this->hasMany(UsedSparepart::class, 'request_id');
    }
    // Relasi ke department tujuan
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_to');
    }
}
