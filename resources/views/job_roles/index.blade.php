@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Job Roles</h1>

        <a href="{{ route('job-roles.create') }}" class="btn btn-primary mb-3">Create New Job Role</a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Kompartemen</th>
                    <th>Departemen</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($job_roles as $jobRole)
                    <tr>
                        <td>{{ $jobRole->company->name ?? 'N/A' }}</td>
                        <td>{{ $jobRole->kompartemen->name ?? 'N/A' }}</td>
                        <td>{{ $jobRole->departemen->name ?? 'N/A' }}</td>
                        <td>{{ $jobRole->nama_jabatan }}</td>
                        <td>{{ $jobRole->deskripsi }}</td>
                        <td>
                            <a href="{{ route('job-roles.edit', $jobRole) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('job-roles.destroy', $jobRole) }}" method="POST" style="display:inline;">
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
