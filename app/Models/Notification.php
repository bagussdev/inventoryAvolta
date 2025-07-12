<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'role_id',
        'department_id',
        'store_id',
        'triggered_by',
        'type',
        'title',
        'message',
        'reference_type',
        'reference_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // Relasi ke user penerima notifikasi (jika spesifik)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke role target (jika disebar berdasarkan role)
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Relasi ke department target
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Relasi ke store target
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // Relasi ke user yang memicu notifikasi
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    // Helper: apakah notifikasi sudah dibaca?
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}
