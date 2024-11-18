@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Master Data Job Roles</h2>

        <a href="{{ route('job-roles.create') }}" class="btn btn-primary mb-3">Buat Info Jabatan Baru</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Dropdown for Company Selection -->
        <div class="form-group mb-3">
            <label for="companyDropdown">Pilih Perusahaan</label>
            <select id="companyDropdown" class="form-control">
                <option value="">-- Semua Perusahaan --</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Dropdown for Kompartemen Selection -->
        <div class="form-group mb-3">
            <label for="kompartemenDropdown">Pilih Kompartemen</label>
            <select id="kompartemenDropdown" class="form-control" disabled>
                <option value="">-- Semua Kompartemen --</option>
            </select>
        </div>

        <!-- Dropdown for Departemen Selection -->
        <div class="form-group mb-3">
            <label for="departemenDropdown">Pilih Departemen</label>
            <select id="departemenDropdown" class="form-control" disabled>
                <option value="">-- Semua Departemen --</option>
            </select>
        </div>

        <hr class="mt-3 mb-3" style="width: 80%; margin:auto">

        <!-- Table to display Job Roles -->
        <table id="jobRolesTable" class="table table-bordered table-striped table-hover mt-3">
            <thead>
                <tr>
                    <th>Perusahaan</th>
                    <th>Nama Jabatan</th>
                    <th>Deskripsi</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
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
            let jobRolesTable = $('#jobRolesTable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                data: [], // Start with empty data
                columns: [{
                        data: 'perusahaan',
                        title: 'Perusahaan'
                    }, {
                        data: 'kompartemen',
                        title: 'Kompartemen'
                    }, {
                        data: 'departemen',
                        title: 'Departemen'
                    }, {
                        data: 'nama_jabatan',
                        title: 'Nama Jabatan'
                    },
                    {
                        data: 'description',
                        title: 'Deskripsi'
                    },
                    {
                        data: 'actions',
                        title: 'Actions',
                        width: '12.5%',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Load Kompartemen based on selected company
            $('#companyDropdown').change(function() {
                let companyId = $(this).val();
                if (companyId) {
                    $.ajax({
                        url: '/get-kompartemen',
                        method: 'GET',
                        data: {
                            company_id: companyId
                        },
                        success: function(data) {
                            $('#kompartemenDropdown').empty().append(
                                '<option value="">-- Semua Kompartemen --</option>');
                            $.each(data, function(key, value) {
                                $('#kompartemenDropdown').append('<option value="' +
                                    value.id + '">' + value.name + '</option>');
                            });
                            $('#kompartemenDropdown').prop('disabled', false);
                        },
                        error: function() {
                            alert('Failed to fetch Kompartemen.');
                        }
                    });
                } else {
                    $('#kompartemenDropdown').prop('disabled', true).empty().append(
                        '<option value="">-- Semua Kompartemen --</option>');
                    $('#departemenDropdown').prop('disabled', true).empty().append(
                        '<option value="">-- Semua Departemen --</option>');
                    jobRolesTable.clear().draw();
                }
            });

            // Load Departemen based on selected Kompartemen
            $('#kompartemenDropdown').change(function() {
                let kompartemenId = $(this).val();
                if (kompartemenId) {
                    $.ajax({
                        url: '/get-departemen',
                        method: 'GET',
                        data: {
                            kompartemen_id: kompartemenId
                        },
                        success: function(data) {
                            $('#departemenDropdown').empty().append(
                                '<option value="">-- Semua Departemen --</option>');
                            $.each(data, function(key, value) {
                                $('#departemenDropdown').append('<option value="' +
                                    value.id + '">' + value.name + '</option>');
                            });
                            $('#departemenDropdown').prop('disabled', false);
                        },
                        error: function() {
                            alert('Failed to fetch Departemen.');
                        }
                    });
                } else {
                    $('#departemenDropdown').prop('disabled', true).empty().append(
                        '<option value="">-- Semua Departemen --</option>');
                    jobRolesTable.clear().draw();
                }
            });

            // Load Job Roles when a dropdown changes
            $('#companyDropdown, #kompartemenDropdown, #departemenDropdown').change(loadJobRoles);

            // Load Job Roles based on selected filters
            function loadJobRoles() {
                let companyId = $('#companyDropdown').val();
                let kompartemenId = $('#kompartemenDropdown').val();
                let departemenId = $('#departemenDropdown').val();

                $.ajax({
                    url: '/get-job-roles',
                    method: 'GET',
                    data: {
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    success: function(data) {
                        jobRolesTable.clear().rows.add(data).draw();
                    },
                    error: function() {
                        alert('Failed to fetch Job Roles.');
                    }
                });
            }

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
        });
    </script>
@endsection
