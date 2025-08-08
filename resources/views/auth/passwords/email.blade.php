@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Forgot Password</h4>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3 row">
                        <label for="email" class="col-md-3 col-form-label text-md-end">Email</label>
                        <div class="col-md-6">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-3">
                            <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
                            <a href="{{ route('login') }}" class="btn btn-link">Back to login</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
