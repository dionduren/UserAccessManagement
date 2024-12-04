@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Master Data Job Roles</h2>

        <a href="{{ route('job-roles.create') }}" class="btn btn-primary mb-3">Buat Job Role</a>

        <!-- Success Message -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

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

        <!-- Table to display Job Roles -->
        <table id="jobRolesTable" class="table table-bordered table-striped table-hover mt-3">
            <thead>
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
                    <!-- Job Role details will be dynamically loaded here -->
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

            let masterData = {}; // To store JSON data for efficient lookups

            let jobRolesTable = $('#jobRolesTable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                data: [], // Start with empty data
                columns: [{
                        data: 'company',
                        title: 'Perusahaan'
                    }, {
                        data: 'kompartemen',
                        title: 'Kompartemen'
                    }, {
                        data: 'departemen',
                        title: 'Departemen'
                    }, {
                        data: 'job_role',
                        title: 'Nama Jabatan'
                    },
                    {
                        data: 'deskripsi',
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

            // Fetch master data and initialize the page
            $.ajax({
                url: '/storage/master_data.json',
                dataType: 'json',
                success: function(data) {
                    masterData = data;

                    // Populate company dropdown
                    populateDropdown('#companyDropdown', data, 'company_id', 'company_name');
                },
                error: function() {
                    alert('Failed to load master data.');
                }
            });

            // Handle company dropdown change
            $('#companyDropdown').on('change', function() {
                const companyId = $(this).val();

                resetDropdowns(['#kompartemenDropdown', '#departemenDropdown']);
                let companyData = masterData.find(c => c.company_id == companyId);

                if (companyData) {
                    // Populate kompartemen dropdown
                    populateDropdown('#kompartemenDropdown', companyData.kompartemen, 'id', 'name');

                    // Populate departemen_without_kompartemen
                    populateDropdown('#departemenDropdown', companyData.departemen_without_kompartemen,
                        'id', 'name');
                }

                loadJobRoles();
            });

            // Handle kompartemen dropdown change
            $('#kompartemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $(this).val();

                resetDropdowns(['#departemenDropdown']);
                let companyData = masterData.find(c => c.company_id == companyId);
                let kompartemenData = companyData?.kompartemen.find(k => k.id == kompartemenId);

                if (kompartemenData?.departemen.length) {
                    // Populate departemen dropdown based on selected kompartemen
                    populateDropdown('#departemenDropdown', kompartemenData.departemen, 'id', 'name');
                }

                loadJobRoles();
            });

            // Handle departemen dropdown change
            $('#departemenDropdown').on('change', function() {
                loadJobRoles();
            });

            // Load job roles based on selected filters
            function loadJobRoles() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $('#kompartemenDropdown').val();
                const departemenId = $('#departemenDropdown').val();

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

            // Helper function to populate dropdowns
            function populateDropdown(selector, items, valueField, textField) {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');
                if (items?.length) {
                    dropdown.prop('disabled', false);
                    items.forEach(item => {
                        dropdown.append(`<option value="${item[valueField]}">${item[textField]}</option>`);
                    });
                } else {
                    dropdown.prop('disabled', true);
                }
            }

            // Helper function to reset dropdowns
            function resetDropdowns(selectors) {
                selectors.forEach(selector => {
                    $(selector).empty().append('<option value="">-- Select --</option>').prop('disabled',
                        true);
                });
            }

            /// Show Job Role Details in Modal
            $(document).on('click', '.show-job-role', function(e) {
                e.preventDefault();
                const jobRoleId = $(this).data('id');

                if (!jobRoleId) {
                    alert('Job Role ID is missing.');
                    return;
                }

                $.ajax({
                    url: `/job-roles/${jobRoleId}`,
                    method: 'GET',
                    success: function(response) {
                        $('#modal-job-role-details').html(response);
                        $('#showJobRoleModal').modal('show');
                    },
                    error: function() {
                        $('#modal-job-role-details').html(
                            '<p class="text-danger">Unable to load job role details.</p>');
                    },
                });
            });

            // Close modal event
            $(document).on('click', '.close', function() {
                $('#showJobRoleModal').modal('hide');
            });
        });
    </script>
@endsection
