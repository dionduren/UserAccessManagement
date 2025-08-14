<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\EmailChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $pendingRequest = EmailChangeRequest::where('user_id', $user->id)->where('status', 'pending')->latest()->first();

        return view('profile.index', compact('user', 'pendingRequest'));
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        $canEditUsername = $user->hasAnyRole(['Super Admin', 'Helpdesk']);
        if ($canEditUsername) {
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)];
        }

        $validated = $request->validate($rules);

        // Only change username if allowed
        if ($canEditUsername && isset($validated['username'])) {
            $user->username = $validated['username'];
        }

        $user->name  = $validated['name'];
        $user->email = $validated['email'] ?? $user->email;
        $user->save();

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'Password updated.');
    }

    public function requestEmailChange(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'new_email' => ['required', 'email', 'max:255', 'different:current_email', Rule::unique('users', 'email')],
        ], [
            'new_email.different' => 'New email must be different from current email.',
        ]);

        // Upsert a single pending request per user
        $ecr = EmailChangeRequest::updateOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            [
                'username'      => $user->username,
                'current_email' => $user->email,
                'new_email'     => strtolower($request->input('new_email')),
                'status'        => 'pending',
                'token'         => \Str::random(64),
            ]
        );

        return back()->with('success', 'Email change request submitted. Awaiting approval.');
    }
}
