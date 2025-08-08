@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ isset($user) ? 'Edit User' : 'Add New User' }}</h1>

        <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
            @csrf
            @if (isset($user))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control"
                    value="{{ $user->name ?? old('name') }}" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                    value="{{ $user->username ?? old('username') }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email (optional)</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="{{ $user->email ?? old('email') }}">
            </div>

            @if (!isset($user))
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            @endif

            <div class="mb-3">
                <label for="roles" class="form-label">Assign Roles</label>
                <select name="roles[]" id="roles" class="form-select" multiple>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}"
                            {{ isset($user) && $user->roles->contains($role->id) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update User' : 'Create User' }}</button>
        </form>
    </div>
@endsection
