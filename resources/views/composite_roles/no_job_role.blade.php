@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Composite Roles Without Job Role</h1>

        @if ($compositeRoles->isEmpty())
            <p>No composite roles without job roles found.</p>
        @else
            <table class="table table-bordered table-striped table-hover mt-3">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Composite Role Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compositeRoles as $role)
                        <tr>
                            <td>{{ $role->company->name ?? 'N/A' }}</td>
                            <td>{{ $role->nama }}</td>
                            <td>{{ $role->deskripsi ?? 'None' }}</td>
                            <td>
                                <a href="{{ route('composite-roles.edit', $role->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('composite-roles.destroy', $role->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
