@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Single Roles</h1>

        <a href="{{ route('single-roles.create') }}" class="btn btn-primary mb-3">Create New Single Role</a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($single_roles as $singleRole)
                    <tr>
                        <td>{{ $singleRole->name }}</td>
                        <td>{{ $singleRole->description }}</td>
                        <td>
                            <a href="{{ route('single-roles.edit', $singleRole) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('single-roles.destroy', $singleRole) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
