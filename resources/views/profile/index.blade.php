@extends('layouts.app')

@section('content')
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">Profile Information</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.updateInfo') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input name="name" type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @php
                                $canEditUsername = auth()
                                    ->user()
                                    ->hasAnyRole(['Super Admin', 'Helpdesk']);
                            @endphp
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input name="username" type="text"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $user->username) }}" {{ $canEditUsername ? '' : 'readonly' }}>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @unless ($canEditUsername)
                                    <small class="text-muted">Contact Helpdesk or Super Admin to change username.</small>
                                @endunless
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input name="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block">Use the form on the right to request an email
                                    change.</small>
                            </div>

                            <button class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">Change Password</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.updatePassword') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input name="current_password" type="password"
                                    class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input name="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input name="password_confirmation" type="password" class="form-control" required>
                            </div>
                            <button class="btn btn-warning">Update Password</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">Request Email Change</div>
                    <div class="card-body">
                        @if ($pendingRequest)
                            <div class="alert alert-info">
                                Pending request to change from <strong>{{ $pendingRequest->current_email }}</strong>
                                to <strong>{{ $pendingRequest->new_email }}</strong>.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.requestEmailChange') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">New Email</label>
                                <input name="new_email" type="email"
                                    class="form-control @error('new_email') is-invalid @enderror"
                                    value="{{ old('new_email') }}" required>
                                @error('new_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button class="btn btn-outline-primary">Submit Request</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
