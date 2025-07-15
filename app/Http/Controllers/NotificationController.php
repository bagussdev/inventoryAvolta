<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = Notification::with(['triggeredBy', 'role', 'department', 'store']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('triggeredBy', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('role', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('store', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $notifications = $perPage === 'all'
            ? $query->latest()->get()
            : $query->latest()->paginate($perPage)->appends($request->query());


        return view('notification_preferences.list', compact('notifications', 'perPage'));
    }


    public function unreadCount()
    {
        $user = Auth::user();

        $query = Notification::query()
            ->whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('role_id', $user->role_id);

                        if (in_array($user->role_id, [3, 4]) && $user->department_id) {
                            $q2->where('department_id', $user->department_id);
                        }

                        if ($user->role_id === 5 && $user->store_id) {
                            $q2->where('store_id', $user->store_id);
                        }

                        // Untuk master biarkan tanpa filter
                    });
            });

        return response()->json(['count' => $query->count()]);
    }

    public function html()
    {
        $user = Auth::user();

        $unreadNotifications = Notification::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('role_id', $user->role_id)
                ->orWhere('department_id', $user->department_id)
                ->orWhere('store_id', $user->store_id);
        })->whereNull('read_at')->latest()->take(10)->get();

        return view('partials.notifications-list', compact('unreadNotifications'));
    }

    public function markAsRead()
    {
        $user = Auth::user();

        $query = Notification::whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('role_id', $user->role_id)
                            ->when(in_array($user->role_id, [3, 4]), function ($q3) use ($user) {
                                // Staff dan SPV harus department cocok
                                $q3->where('department_id', $user->department_id);
                            })
                            ->when($user->role_id === 5, function ($q4) use ($user) {
                                $q4->where(function ($query) use ($user) {
                                    $query->where('store_id', $user->store_location)
                                        ->where('role_id', 5);
                                });
                            });
                    });
            });

        $query->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read']);
    }

    public function lastUpdated()
    {
        $user = Auth::user();

        $query = Notification::query()
            ->whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('role_id', $user->role_id);

                        if (in_array($user->role_id, [3, 4]) && $user->department_id) {
                            $q2->where('department_id', $user->department_id);
                        }

                        if ($user->role_id === 5 && $user->store_id) {
                            $q2->where('store_id', $user->store_id);
                        }

                        // Role 1 (master) tidak difilter
                    });
            });

        $lastUpdated = $query->max('updated_at');

        return response()->json(['last_updated' => $lastUpdated]);
    }
}
