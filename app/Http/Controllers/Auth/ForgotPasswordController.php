<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    // Prefill the form with current user email when logged in
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email', [
            'defaultEmail' => Auth::user()->email ?? old('email')
        ]);
    }

    // Enforce ownership when authenticated + add simple throttling
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // If the requester is logged in, they may only request for their own email
        if (Auth::check() && strcasecmp($request->input('email'), Auth::user()->email) !== 0) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'You can only request a reset for your own account email.']);
        }

        // Optional: throttle by email+IP (5 tries per minute)
        $key = 'pwd-reset:' . Str::lower($request->input('email')) . '|ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Too many requests. Please try again in a minute.']);
        }
        RateLimiter::hit($key, 60);

        $response = $this->broker()->sendResetLink($request->only('email'));

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    public function changeEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|unique:users,email']);

        $req = EmailChangeRequest::create([
            'user_id' => Auth::id(),
            'current_email' => Auth::user()->email,
            'new_email' => $request->input('email'),
            'status' => 'pending',
            'token' => Str::random(64),
        ]);

        // Optionally notify Super Admins here if you don’t use the dedicated controller
        // User::role('Super Admin')->get()->each(fn ($admin) => $admin->notify(new EmailChangeRequestSubmitted($req)));

        return redirect()->route('password.request')
            ->with('status', 'Email change request submitted for approval.');
    }

    // Normalize email before sending link
    protected function validateEmail(Request $request)
    {
        $request->merge(['email' => strtolower(trim($request->input('email')))]);
        $request->validate(['email' => 'required|email']);
    }
}
