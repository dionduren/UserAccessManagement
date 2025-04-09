@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Job Roles</h1>

    <a href="{{ route('job-roles.create') }}" class="btn btn-primary mb-3">Create New Job Role</a>

    @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <table id="job_roles_table" class="table table-striped">
        <thead>
            <tr>
                <th>Company</th>
                <th>Kompartemen</th>
                <th>Departemen</th>
                <th>Name</th>
                {{-- <th>Composite Role</th> --}}
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($job_roles as $jobRole)
            <tr>
                <td>{{ $jobRole->company->nama ?? 'N/A' }}</td>
                <td>{{ $jobRole->kompartemen->nama ?? 'N/A' }}</td>
                <td>{{ $jobRole->departemen->nama ?? 'N/A' }}</td>
                <td>{{ $jobRole->nama_jabatan }}</td>
                {{-- <td>{{ $jobRole->compositeRole->nama ?? 'Not Assigned' }}</td> --}}
                <td>
                    <a href="#" data-id="{{ $jobRole->id }}" class="btn btn-info btn-sm show-job-role"
                        data-toggle="modal" data-target="#showJobRoleModal">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('job-roles.edit', $jobRole) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('job-roles.destroy', $jobRole) }}" method="POST"
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

<!-- Modal for Job Role Details -->
<div class="modal fade" id="showJobRoleModal" tabindex="-1" aria-labelledby="showJobRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showJobRoleModalLabel">Job Role Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-job-role-details">
                <!-- Job Role Details will be loaded here dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#job_roles_table').DataTable({
            searching: true,
            processing: false,
            serverSide: false
        });

        // Show Job Role Details in Modal
        $(document).on('click', '.show-job-role', function(e) {
            e.preventDefault();
            let jobRoleId = $(this).data('id');

            // Fetch the job role details using AJAX
            $.ajax({
                url: `/job-roles/${jobRoleId}`, // Ensure this is the correct URL to fetch data
                method: 'GET',
                success: function(response) {
                    $('#modal-job-role-details').html(
                        response); // Populate modal with response
                    $('#showJobRoleModal').modal('show'); // Open the modal
                },
                error: function() {
                    $('#modal-job-role-details').html(
                        '<p class="text-danger">Unable to load job role details.</p>');
                }
            });
        });

        $(document).on('click', '.close', function() {
            $('#showJobRoleModal').modal('hide');
        });
    });
</script>
@endsection