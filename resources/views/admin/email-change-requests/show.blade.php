@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Review Email Change</h4>
            </div>

            <div class="card-body">
                <p class="mb-1"><strong>User:</strong> {{ $emailChangeRequest->user->name }}</p>
                <p class="mb-1"><strong>Current:</strong> {{ $emailChangeRequest->current_email }}</p>
                <p class="mb-0"><strong>New:</strong> {{ $emailChangeRequest->new_email }}</p>
            </div>

            <div class="card-footer bg-white">
                <form method="POST" action="{{ route('admin.email-change-requests.approve', $emailChangeRequest) }}"
                    class="d-inline">
                    @csrf
                    <button class="btn btn-success">Approve</button>
                </form>

                <form method="POST" action="{{ route('admin.email-change-requests.reject', $emailChangeRequest) }}"
                    class="d-inline ms-2">
                    @csrf
                    <input type="text" name="reason" class="form-control d-inline w-auto"
                        placeholder="Reason (optional)">
                    <button class="btn btn-danger">Reject</button>
                </form>
            </div>
        </div>
    </div>
@endsection
