<?php

namespace App\Listeners;

use App\Models\UserLoginDetail;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;

class LogAuthenticationEvents
{
    public function handle($event): void
    {
        $request = request();
        $common = [
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'session_id' => $request?->session()?->getId(),
            'request_id' => $request?->headers->get('X-Request-Id'),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($event instanceof Login) {
            DB::table('auth_logs')->insert(array_merge($common, [
                'event'    => 'login',
                'user_id'  => $event->user->id ?? null,
                'username' => $event->user->username ?? null,
                'email'    => $event->user->email ?? null,
            ]));

            // Update per-user login detail
            if ($event->user?->id) {
                UserLoginDetail::updateOrCreate(
                    ['user_id' => $event->user->id],
                    [
                        'last_login_at' => now(),
                        'last_login_ip' => request()?->ip(),
                    ]
                );
            }
        } elseif ($event instanceof Logout) {
            DB::table('auth_logs')->insert(array_merge($common, [
                'event'    => 'logout',
                'user_id'  => $event->user->id ?? null,
                'username' => $event->user->username ?? null,
                'email'    => $event->user->email ?? null,
            ]));
        } elseif ($event instanceof Failed) {
            DB::table('auth_logs')->insert(array_merge($common, [
                'event'    => 'failed',
                'user_id'  => $event->user?->id,
                'username' => $event->credentials['username'] ?? null,
                'email'    => $event->credentials['email'] ?? null,
            ]));
        }

        if ($event instanceof PasswordReset) {
            DB::table('auth_logs')->insert(array_merge($common, [
                'event'    => 'password_reset',
                'user_id'  => $event->user->id ?? null,
                'username' => $event->user->username ?? null,
                'email'    => $event->user->email ?? null,
            ]));
            return;
        }
    }
}
