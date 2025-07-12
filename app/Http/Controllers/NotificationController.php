<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Notification::query()->latest();

        // Filtering berdasarkan role dan store
        if ($user->role_id === 1) {
            // Master – lihat semua
        } elseif (in_array($user->role_id, [2, 3, 4])) {
            // Manager/SPV/Staff – filter by department
            $query->where('department_id', $user->department_id)
                ->where(function ($q) use ($user) {
                    $q->where('role_id', $user->role_id)
                        ->orWhereNull('user_id'); // untuk notifikasi massal
                });
        } else {
            // User biasa – filter berdasarkan store
            $query->where('store_id', $user->store_id)
                ->orWhere('user_id', $user->id);
        }

        $notifications = $query->get();

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $user = Auth::user();

        $query = Notification::query()->whereNull('read_at');

        if ($user->role_id === 1) {
            // Master
        } elseif (in_array($user->role_id, [2, 3, 4])) {
            $query->where('department_id', $user->department_id)
                ->where(function ($q) use ($user) {
                    $q->where('role_id', $user->role_id)
                        ->orWhereNull('user_id');
                });
        } else {
            $query->where('store_id', $user->store_id)
                ->orWhere('user_id', $user->id);
        }

        return response()->json(['count' => $query->count()]);
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
                                // User biasa berdasarkan store
                                $q4->where('store_id', $user->store_id);
                            });
                    });
            });

        $query->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read']);
    }


    public function lastUpdated()
    {
        $user = Auth::user();

        $query = Notification::query()->whereNull('read_at');

        if ($user) {
            if ($user->role_id === 1) {
                // Master lihat semua
            } elseif (in_array($user->role_id, [2, 3, 4])) {
                $query->where('department_id', $user->department_id)
                    ->where(function ($q) use ($user) {
                        $q->where('role_id', $user->role_id)
                            ->orWhere('user_id', $user->id);
                    });
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('store_id', $user->store_id)
                        ->orWhere('user_id', $user->id);
                });
            }
        }

        $lastUpdated = $query->max('updated_at');

        return response()->json(['last_updated' => $lastUpdated]);
    }
}
