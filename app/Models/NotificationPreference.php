<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'role_id',
        'department_id',
        'allowed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    // App\Models\NotificationPreference.php
    public static function isAllowed(string $type, int $roleId): bool
    {
        return self::where('type', $type)
            ->where('role_id', $roleId)
            ->exists();
    }
}
