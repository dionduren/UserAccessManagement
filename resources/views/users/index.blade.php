@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">

                <h1>User Management</h1>
            </div>
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Add New User</a>

                <table id="users-table" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Perusahaan</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ optional($user->loginDetail)->company_code }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this user?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        $(function() {
            $('#users-table').DataTable({
                pageLength: 10,
                lengthChange: true,
                ordering: true,
                searching: true,
                columnDefs: [{
                        orderable: false,
                        searchable: false,
                        targets: -1
                    } // Actions column
                ]
            });
        });
    </script>
@endsection
