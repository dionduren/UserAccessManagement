<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
        // Rate limit login attempts: 5 attempts per minute
        $this->middleware('throttle:5,1')->only('login');
    }

    // Use 'username' for authentication instead of 'email'
    public function username()
    {
        return 'username';
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated($request, $user)
    {
        log_audit_trail(
            'login',
            'App\Models\User',
            $user->id,
            null,
            ['username' => $user->username, 'name' => $user->name],
            $user->id
        );
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(\Illuminate\Http\Request $request)
    {
        log_audit_trail(
            'login_failed',
            'App\Models\User',
            null,
            [
                'attempted_username' => $request->input($this->username()),
                'failed_at' => now()->toDateTimeString(),
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'attempt_count' => $this->getRecentFailedAttempts($request->ip()),
            ],
            null
        );

        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function getRecentFailedAttempts($ip)
    {
        return \App\Models\AuditTrail::where('activity_type', 'login_failed')
            ->where('ip_address', $ip)
            ->where('logged_at', '>=', now()->subHours(1))
            ->count();
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        if ($user) {
            log_audit_trail(
                'logout',
                'App\Models\User',
                $user->id,
                ['username' => $user->username, 'name' => $user->name],
                null,
                $user->id
            );
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->loggedOut($request) ?: redirect('/');
    }
}
