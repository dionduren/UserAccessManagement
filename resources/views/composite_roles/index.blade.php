@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Composite Roles</h1>

        <a href="{{ route('composite-roles.create') }}" class="btn btn-primary mb-3">Create New Composite Role</a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <table id="composite_roles_table" class="table table-striped">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Composite Role Name</th>
                    <th>Job Role</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Include the modal for composite role details -->
    <div class="modal fade" id="showCompositeRoleModal" tabindex="-1" aria-labelledby="showCompositeRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showCompositeRoleModalLabel">Composite Role Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-composite-role-details">
                    <!-- Details loaded dynamically via AJAX -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable with AJAX
            $('#composite_roles_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('composite-roles.ajax') }}',
                columns: [{
                        data: 'company',
                        name: 'company'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'job_role',
                        name: 'job_role'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Show Composite Role Details in Modal
            $(document).on('click', '.show-composite-role', function(e) {
                e.preventDefault();
                const compositeRoleId = $(this).data('id');

                // Fetch the composite role details using AJAX
                $.ajax({
                    url: `/composite-roles/${compositeRoleId}`, // Ensure this is the correct URL
                    method: 'GET',
                    success: function(response) {
                        $('#modal-composite-role-details').html(
                        response); // Populate modal with response
                        $('#showCompositeRoleModal').modal('show'); // Open the modal
                    },
                    error: function() {
                        $('#modal-composite-role-details').html(
                            '<p class="text-danger">Unable to load composite role details.</p>'
                            );
                    }
                });
            });

            $(document).on('click', '.close', function() {
                $('#showCompositeRoleModal').modal('hide');
            });
        });
    </script>
@endsection
