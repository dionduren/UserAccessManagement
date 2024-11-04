@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Composite Roles</h1>

        <a href="{{ route('composite-roles.create') }}" class="btn btn-primary mb-3">Create New Composite Role</a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Job Role</th>
                    <th>Company</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($composite_roles as $role)
                    <tr>
                        <td>{{ $role->nama }}</td>
                        <td>{{ $role->deskripsi }}</td>
                        <td>{{ $role->jobRole->nama_jabatan ?? 'N/A' }}</td>
                        <td>{{ $role->company->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('composite_roles.edit', $role) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('composite_roles.destroy', $role) }}" method="POST"
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
