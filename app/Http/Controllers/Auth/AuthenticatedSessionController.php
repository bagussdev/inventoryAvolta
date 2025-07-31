<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\NotificationService;
use App\Models\NotificationPreference;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    private function getNotificationTargets(string $type, int $departmentId = null): array
    {
        $preferences = NotificationPreference::where('type', $type)->pluck('role_id')->toArray();

        $targets = [];

        foreach ($preferences as $roleId) {
            if (in_array($roleId, [1])) { // Master tanpa department
                $targets[] = ['role_id' => $roleId];
            } elseif ($departmentId) {
                $targets[] = ['role_id' => $roleId, 'department_id' => $departmentId];
            }
        }

        return $targets;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $department = $user->department->name ?? '-';
        $store = $user->location->name ?? '-';

        // Kirim notifikasi login
        $targets = $this->getNotificationTargets('login', $user->department_id);
        foreach ($targets as &$target) {
            $target['store_id'] = $user->store_id;
        }
        unset($target);

        NotificationService::send(
            $targets,
            'login',
            'User Logged In',
            "<b>{$user->name}</b> from <b>{$department}</b> at <b>{$store}</b> has just logged in.",
            'users',
            $user->id
        );
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $department = $user->department->name ?? '-';
        $store = $user->location->name ?? '-';

        // Kirim notifikasi logout
        $targets = $this->getNotificationTargets('logout', $user->department_id);
        foreach ($targets as &$target) {
            $target['store_id'] = $user->store_id;
        }
        unset($target);

        NotificationService::send(
            $targets,
            'logout',
            'User Logged Out',
            "<b>{$user->name}</b> from <b>{$department}</b> at <b>{$store}</b> has just logged out.",
            'users',
            $user->id
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return request()->expectsJson()
            ? response()->json(['status' => 'logged_out'])
            : redirect('/');
    }
}
