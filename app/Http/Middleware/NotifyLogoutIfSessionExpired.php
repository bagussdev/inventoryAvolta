<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\NotificationService;

class NotifyLogoutIfSessionExpired
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() && Session::has('was_authenticated')) {
            $user = Session::get('was_authenticated');

            // Kirim notifikasi logout karena idle/session timeout
            $department = $user->department->name ?? '-';
            $store = $user->location->name ?? '-';

            $targets = NotificationService::getTargets('logout', $user->department_id);
            foreach ($targets as &$target) {
                $target['store_id'] = $user->store_id;
            }

            NotificationService::send(
                $targets,
                'logout',
                'User Logged Out (Session Timeout)',
                "<b>{$user->name}</b> from <b>{$department}</b> at <b>{$store}</b> has been auto-logged out due to inactivity.",
                'users',
                $user->id
            );

            Session::forget('was_authenticated');
        }

        if (Auth::check()) {
            session(['was_authenticated' => Auth::user()]);
        }

        return $next($request);
    }
}
