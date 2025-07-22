<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'no_telfon',
        'password',
        'store_location',
        'role_id',
        'department_id',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship to Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relationship to Department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function location()
    {
        return $this->belongsTo(Store::class, 'store_location');
    }

    // public function permissions()
    // {
    //     return $this->role->permissions();
    // }
    public function permissions()
    {
        return $this->role ? $this->role->permissions() : collect();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role
            && $this->role->permissions
            && $this->role->permissions->pluck('name')->contains($permission);
    }
    // Sebagai PIC Staff di Maintenance
    public function maintenancesAsStaff(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'picstaff');
    }

    // Sebagai Supervisor yang mengkonfirmasi Maintenance
    public function maintenancesConfirmed(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'confirmby');
    }
}
