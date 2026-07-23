<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Models\NotificationPreference;
use Illuminate\Support\Carbon;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            // 'recaptcha_token' => ['required', 'string'], // hapus baris ini + verifikasi di bawah kalau tidak pakai reCAPTCHA
        ]);

        // --- (Opsional) Verifikasi reCAPTCHA v3 ---
        // try {
        //     $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        //         'secret'   => env('RECAPTCHA_SECRET_KEY'),
        //         'response' => $request->recaptcha_token,
        //         'remoteip' => $request->ip(),
        //     ])->json();

        //     if (empty($verify['success']) || ($verify['score'] ?? 0) < 0.5) {
        //         return back()->withErrors(['email' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.']);
        //     }
        // } catch (\Throwable $e) {
        //     Log::warning('reCAPTCHA verify error', ['err' => $e->getMessage()]);
        //     return back()->withErrors(['email' => 'Verifikasi gagal. Coba lagi beberapa saat.']);
        // }

        // --- Kirim reset link + tangkap limit pengiriman ---
        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (TransportExceptionInterface $e) {
            // Symfony Mailer (Laravel 10/11)
            if ($this->isGmailSendLimit($e->getMessage())) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Sorry, we have reached the sending limit. Please try again later.']);
            }
            Log::error('Mail transport error', ['err' => $e->getMessage()]);
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Pengiriman email gagal. Coba lagi beberapa saat.']);
        } catch (\Throwable $e) {
            // Guard umum
            if ($this->isGmailSendLimit($e->getMessage())) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Sorry, we have reached the sending limit. Please try again later.']);
            }
            Log::error('Reset password mail failed', ['err' => $e->getMessage()]);
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Pengiriman email gagal. Coba lagi beberapa saat.']);
        }

        // --- Notifikasi ke Master (global) ---
        try {
            $targets = $this->getNotificationTargets('forgot_password_request', null);
            if (empty($targets)) $targets = [['role_id' => 1]]; // fallback Master

            $title   = 'Forgot Password Requested';
            $reqMail = e($request->input('email'));
            $ip      = $request->ip();
            $ua      = (string) $request->header('User-Agent');
            $when    = Carbon::now()->format('d M Y H:i');

            $message = "Permintaan <b>Forgot Password</b> untuk <b>{$reqMail}</b> pada <b>{$when}</b> dari IP <b>{$ip}</b> (UA: {$ua}).";

            NotificationService::send(
                $targets,
                'forgot_password_request',
                $title,
                $message,
                'auth',
                0
            );
        } catch (\Throwable $e) {
            Log::warning('Notify master (forgot password) failed', ['err' => $e->getMessage()]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    /**
     * Deteksi pesan limit Gmail (quota harian).
     */
    private function isGmailSendLimit(string $msg): bool
    {
        $m = mb_strtolower($msg);
        return str_contains($m, '5.4.5 daily user sending limit exceeded')
            || str_contains($m, 'daily user sending limit exceeded')
            || str_contains($m, '550 5.4.5')
            || str_contains($m, 'user rate limit exceeded');
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
}
