<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Kirim notifikasi ke 1 atau lebih user/role.
     *
     * @param array $targets Format: [
     *     ['user_id' => 1],
     *     ['role_id' => 4, 'department_id' => 2],
     *     ['store_id' => 3],
     * ]
     * @param string $type contoh: incident, request, maintenance, transaction
     * @param string $title Judul notifikasi
     * @param string $message Pesan utama
     * @param string|null $referenceType Contoh: incidents, requests
     * @param int|null $referenceId ID referensi dari entitas
     */
    public static function send(
        array $targets,
        string $type,
        string $title,
        string $message,
        string $referenceType,
        int $referenceId
    ) {
        foreach ($targets as $target) {
            Notification::create([
                'user_id'        => $target['user_id'] ?? null,
                'role_id'        => $target['role_id'] ?? null,
                'department_id'  => $target['department_id'] ?? null,
                'store_id'       => $target['store_id'] ?? null,
                'triggered_by'   => Auth::id(),
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'read_at'        => null,
            ]);
        }
    }
    public static function getTargets(string $type, ?int $departmentId = null): array
    {
        // Khusus kirim ke role Master (role_id = 1)
        return \App\Models\User::where('role_id', 1)->get()->map(function ($user) {
            return [
                'user_id'       => $user->id,
                'role_id'       => $user->role_id,
                'department_id' => $user->department_id,
                'store_id'      => $user->store_id,
            ];
        })->toArray();
    }
}
