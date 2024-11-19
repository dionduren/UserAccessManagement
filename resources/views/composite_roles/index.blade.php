@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Composite Roles</h1>

        <a href="{{ route('composite-roles.create') }}" class="btn btn-primary mb-3">Create New Composite Role</a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <!-- Dropdowns for Filtering -->
        <div class="form-group">
            <label for="companyDropdown">Select Company</label>
            <select id="companyDropdown" class="form-control select2">
                <option value="">-- Select Company --</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="kompartemenDropdown">Select Kompartemen</label>
            <select id="kompartemenDropdown" class="form-control select2" disabled>
                <option value="">-- Select Kompartemen --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="departemenDropdown">Select Departemen</label>
            <select id="departemenDropdown" class="form-control select2" disabled>
                <option value="">-- Select Departemen --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="jobRoleDropdown">Select Job Role</label>
            <select id="jobRoleDropdown" class="form-control select2" disabled>
                <option value="">-- Select Job Role --</option>
            </select>
        </div>

        <!-- DataTable -->
        <table id="composite_roles_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Composite Role Name</th>
                    <th>Job Role</th>
                    <th>Single Roles</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modal for Composite Role Details -->
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
                    <!-- Composite Role details will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize select2
            $('.select2').select2();

            // Initialize DataTable
            let table = $('#composite_roles_table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('composite-roles.data') }}",
                    data: function(d) {
                        d.company_id = $('#companyDropdown').val();
                        d.kompartemen_id = $('#kompartemenDropdown').val();
                        d.departemen_id = $('#departemenDropdown').val();
                        d.job_role = $('#jobRoleDropdown').val();
                    }
                },
                columns: [{
                        data: 'company',
                        name: 'company'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'job_role',
                        name: 'job_role'
                    },
                    {
                        data: 'single_roles',
                        name: 'single_roles',
                        orderable: false,
                        render: function(data) {
                            return data || 'No Single Roles';
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Dropdown change events to reload table data
            $('#companyDropdown, #kompartemenDropdown, #departemenDropdown, #jobRoleDropdown').on('change',
                function() {
                    table.ajax.reload();
                });

            // Populate Kompartemen and Departemen (without Kompartemen) based on selected Company
            $('#companyDropdown').on('change', function() {
                let companyId = $(this).val();
                $('#kompartemenDropdown').prop('disabled', !companyId).empty().append(
                    '<option value="">-- Select Kompartemen --</option>');
                $('#departemenDropdown').prop('disabled', true).empty().append(
                    '<option value="">-- Select Departemen --</option>');
                $('#jobRoleDropdown').prop('disabled', true).empty().append(
                    '<option value="">-- Select Job Role --</option>');

                if (companyId) {
                    // Load Kompartemen
                    $.ajax({
                        url: '/get-kompartemen',
                        data: {
                            company_id: companyId
                        },
                        success: function(data) {
                            data.forEach(item => {
                                $('#kompartemenDropdown').append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });
                            $('#kompartemenDropdown').prop('disabled', false);
                        },
                        error: function() {
                            alert('Failed to load Kompartemen data.');
                        }
                    });

                    // Load Departemen without Kompartemen
                    $.ajax({
                        url: '/get-departemen-by-company',
                        data: {
                            company_id: companyId
                        },
                        success: function(data) {
                            if (data.length > 0) {
                                data.forEach(item => {
                                    $('#departemenDropdown').append(
                                        `<option value="${item.id}">${item.name}</option>`
                                    );
                                });
                                $('#departemenDropdown').prop('disabled', false);
                            }
                        },
                        error: function() {
                            alert('Failed to load Departemen data.');
                        }
                    });
                }
            });

            // Populate Departemen based on selected Kompartemen
            $('#kompartemenDropdown').on('change', function() {
                let kompartemenId = $(this).val();
                $('#departemenDropdown').prop('disabled', !kompartemenId).empty().append(
                    '<option value="">-- Select Departemen --</option>');
                $('#jobRoleDropdown').prop('disabled', true).empty().append(
                    '<option value="">-- Select Job Role --</option>');

                if (kompartemenId) {
                    $.ajax({
                        url: '/get-departemen',
                        data: {
                            kompartemen_id: kompartemenId
                        },
                        success: function(data) {
                            data.forEach(item => {
                                $('#departemenDropdown').append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });
                            $('#departemenDropdown').prop('disabled', false);
                        },
                        error: function() {
                            alert('Failed to load Departemen data.');
                        }
                    });
                }
            });

            // Populate Job Roles based on selected Departemen
            $('#departemenDropdown').on('change', function() {
                let companyId = $('#companyDropdown').val();
                let kompartemenId = $('#kompartemenDropdown').val();
                let departemenId = $(this).val();
                $('#jobRoleDropdown').prop('disabled', !departemenId).empty().append(
                    '<option value="">-- Select Job Role --</option>');

                if (departemenId) {
                    $.ajax({
                        url: '/get-job-roles',
                        data: {
                            company_id: companyId,
                            kompartemen_id: kompartemenId,
                            departemen_id: departemenId
                        },
                        success: function(data) {
                            data.forEach(item => {
                                $('#jobRoleDropdown').append(
                                    `<option value="${item.nama_jabatan}">${item.nama_jabatan}</option>`
                                    );
                            });
                            $('#jobRoleDropdown').prop('disabled', false);
                        },
                        error: function() {
                            alert('Failed to load Job Roles.');
                        }
                    });
                }
            });

            // Display modal for composite role details
            $(document).on('click', '.show-composite-role', function(e) {
                e.preventDefault();
                const compositeRoleId = $(this).data('id');

                $.ajax({
                    url: `/composite-roles/${compositeRoleId}`,
                    method: 'GET',
                    success: function(response) {
                        $('#modal-composite-role-details').html(response);
                        $('#showCompositeRoleModal').modal('show');
                    },
                    error: function() {
                        $('#modal-composite-role-details').html(
                            '<p class="text-danger">Unable to load composite role details.</p>'
                        );
                    }
                });
            });

            // Close modal event
            $(document).on('click', '.close', function() {
                $('#showCompositeRoleModal').modal('hide');
            });
        });
    </script>
@endsection
