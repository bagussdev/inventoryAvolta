<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                $unreadNotifications = Notification::where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere(function ($q) use ($user) {
                            $q->where('role_id', $user->role_id);

                            // Kalau bukan master, cek juga departemen
                            if ($user->role_id != 1 && $user->department_id) {
                                $q->where('department_id', $user->department_id);
                            }

                            // Jika master, abaikan department
                        })
                        ->orWhere(function ($q) use ($user) {
                            if ($user->store_location) {
                                $q->where('store_id', $user->store_location)->where('role_id', $user->role_id);;
                            }
                        });
                })
                    ->orderByDesc('created_at')
                    ->take(10)
                    ->get();

                $view->with('unreadNotifications', $unreadNotifications);
            }
        });
    }
}
