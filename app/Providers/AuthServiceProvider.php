<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();
        // 1. Gate 'isMaster' yang spesifik
        Gate::define('isMaster', function (User $user) {
            // Cek apakah user memiliki role_id = 1 (sesuaikan jika ID Master berbeda)
            // atau cek nama rolenya
            return $user->role_id === 1; // atau return $user->role->name === 'Master';
        });
        // Dynamic permission check using user's role
        Gate::before(function (User $user, $ability) {
            return $user->hasPermission($ability) ? true : null;
        });
    }
}
