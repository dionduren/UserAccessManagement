@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Single Roles</h1>

        <a href="{{ route('single-roles.create') }}" class="btn btn-primary mb-3">Create New Single Role</a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <table id="single_roles_table" class="table table-striped">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Composite Role</th>
                    <th>Single Role Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($single_roles as $singleRole)
                    <tr>
                        <td>{{ $singleRole->company->name ?? 'N/A' }}</td>
                        <td>{{ $singleRole->compositeRole->nama ?? 'Not Assigned' }}</td>
                        <td>{{ $singleRole->nama }}</td>
                        <td>{{ $singleRole->deskripsi }}</td>
                        <td>
                            <a href="#" data-id="{{ $singleRole->id }}" class="btn btn-info btn-sm show-single-role"
                                data-toggle="modal" data-target="#showSingleRoleModal">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('single-roles.edit', $singleRole) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('single-roles.destroy', $singleRole) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal for Single Role Details -->
    <div class="modal fade" id="showSingleRoleModal" tabindex="-1" aria-labelledby="showSingleRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showSingleRoleModalLabel">Single Role Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-single-role-details">
                    <!-- Single Role Details will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#single_roles_table').DataTable({
                searching: true,
                processing: false,
                serverSide: false
            });

            // Show Single Role Details in Modal
            $(document).on('click', '.show-single-role', function(e) {
                e.preventDefault();
                let singleRoleId = $(this).data('id');

                // Fetch the single role details using AJAX
                $.ajax({
                    url: `/single-roles/${singleRoleId}`,
                    method: 'GET',
                    success: function(response) {
                        $('#modal-single-role-details').html(response);
                        $('#showSingleRoleModal').modal('show'); // Open the modal
                    },
                    error: function() {
                        $('#modal-single-role-details').html(
                            '<p class="text-danger">Unable to load single role details.</p>'
                        );
                    }
                });
            });

            $(document).on('click', '.close', function() {
                $('#showSingleRoleModal').modal('hide');
            });
        });
    </script>
@endsection
