<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Models\EmailChangeRequest;
use App\Models\User;
use App\Notifications\EmailChangeRequestResult;
use App\Notifications\EmailChangeRequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EmailChangeRequestController extends Controller
{
    // User: submit new request
    public function store(Request $request)
    {
        $request->validate(['new_email' => 'required|email|unique:users,email']);

        $user = Auth::user();

        $req = EmailChangeRequest::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'current_email' => $user->email,
            'new_email' => $request->new_email,
            'status' => 'pending',
            'token' => Str::random(64),
        ]);

        // Notify all Super Admins
        User::role('Super Admin')->get()->each(fn($admin) => $admin->notify(new EmailChangeRequestSubmitted($req)));

        return back()->with('status', 'Email change request submitted for approval.');
    }

    public function changeEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|unique:users,email']);

        $req = EmailChangeRequest::create([
            'user_id' => Auth::id(),
            'username' => Auth::user()->username,
            'current_email' => Auth::user()->email,
            'new_email' => $request->input('email'),
            'status' => 'pending',
            'token' => Str::random(64),
        ]);

        return redirect()->route('password.request')
            ->with('status', 'Email change request submitted for approval.');
    }

    // Admin: review one
    public function show(EmailChangeRequest $emailChangeRequest)
    {
        return view('admin.email-change-requests.show', compact('emailChangeRequest'));
    }

    // Admin: approve
    public function approve(EmailChangeRequest $emailChangeRequest, Request $request)
    {
        $this->authorizeAdmin();

        if ($emailChangeRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Request already processed.']);
        }

        // Double-check uniqueness at approval time
        if (User::where('email', $emailChangeRequest->new_email)->exists()) {
            return back()->withErrors(['new_email' => 'New email is already taken.']);
        }

        // Update user email
        $emailChangeRequest->user->update(['email' => $emailChangeRequest->new_email]);

        $emailChangeRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Notify requester
        $emailChangeRequest->user->notify(new EmailChangeRequestResult($emailChangeRequest));

        return redirect()->route('admin.email-change-requests.index')->with('status', 'Request approved.');
    }

    // Admin: reject
    public function reject(EmailChangeRequest $emailChangeRequest, Request $request)
    {
        $this->authorizeAdmin();

        if ($emailChangeRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Request already processed.']);
        }

        $emailChangeRequest->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'reason' => $request->input('reason'),
        ]);

        $emailChangeRequest->user->notify(new EmailChangeRequestResult($emailChangeRequest));

        return redirect()->route('admin.email-change-requests.index')->with('status', 'Request rejected.');
    }

    // Admin: list
    public function index()
    {
        $this->authorizeAdmin();
        $requests = EmailChangeRequest::latest()->paginate(20);
        return view('admin.email-change-requests.index', compact('requests'));
    }

    public function data(Request $request)
    {
        $query = EmailChangeRequest::with('user')->latest();

        return DataTables::eloquent($query)
            ->addColumn('user_name', fn($r) => $r->user?->name)
            ->addColumn('action', function ($r) {
                $url = route('admin.email-change-requests.show', $r);
                return '<a href="' . $url . '" class="btn btn-sm btn-primary">Review</a>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403, 'Forbidden');
    }
}
