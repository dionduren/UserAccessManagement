@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Master Data Job Roles</h2>
            </div>
            <div class="card-body">

                <a href="{{ route('job-roles.create') }}" class="btn btn-primary mb-3">Buat Job Role</a>
                <a href="#" id="downloadFlaggedBtn" class="btn btn-outline-danger mb-3 ms-2">
                    <i class="bi bi-download"></i> Download Flagged Data
                </a>

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
                            <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
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
            <div class="modal fade" id="showJobRoleModal" tabindex="-1" aria-labelledby="showJobRoleModalLabel"
                aria-hidden="true">
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

            <!-- Modal for Flagged Info and Change Flagged Status -->
            <div class="modal fade" id="flaggedJobRoleModal" tabindex="-1" aria-labelledby="flaggedJobRoleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="flaggedJobRoleModalLabel">Flagged Info</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="modal-flagged-job-role-details">
                            <!-- Flagged info will be loaded here -->
                            <div class="text-center">
                                <span class="spinner-border" role="status"></span>
                            </div>
                        </div>
                        <div class="modal-footer d-none" id="flagged-job-role-actions">
                            <form id="flaggedJobRoleForm" method="POST">
                                @csrf
                                <input type="hidden" name="job_role_id" id="flagged-job-role-id" value="">
                                <div class="form-group">
                                    <label for="flagged-status">Flagged Status</label>
                                    <select class="form-control" name="flagged" id="flagged-status">
                                        <option value="1">Flagged</option>
                                        <option value="0">Not Flagged</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="flagged-keterangan">Keterangan</label>
                                    <textarea class="form-control" name="keterangan" id="flagged-keterangan" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Flagged Status</button>
                            </form>
                        </div>
                    </div>
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
                    },
                    {
                        data: 'kompartemen',
                        title: 'Kompartemen'
                    },
                    {
                        data: 'departemen',
                        title: 'Departemen'
                    },
                    {
                        data: 'job_role_id',
                        title: 'Kode Job Role'
                    },
                    {
                        data: 'job_role',
                        title: 'Nama Jabatan'
                    },
                    {
                        data: 'deskripsi',
                        title: 'Deskripsi'
                    },
                    {
                        data: 'status',
                        title: 'Status',
                        render: function(data) {
                            if (data === 'Active') {
                                return '<span style="color: #fff; background: #28a745; padding: 2px 8px; border-radius: 4px;">Active</span>';
                            } else if (data === 'Not Active') {
                                return '<span style="color: #fff; background: #dc3545; padding: 2px 8px; border-radius: 4px;">Not Active</span>';
                            }
                            return data;
                        },
                        createdCell: function(td, cellData) {
                            if (cellData === 'Not Active') {
                                $(td).css({
                                    'background': '#dc3545',
                                    'color': '#fff'
                                });
                            } else if (cellData === 'Active') {
                                $(td).css({
                                    'background': '#28a745',
                                    'color': '#fff'
                                });
                            }
                        }
                    },
                    {
                        data: 'flagged',
                        title: 'Flagged',
                        render: function(data) {
                            return data ? 'Yes' : 'No';
                        },
                        createdCell: function(td, cellData, rowData) {
                            if (rowData.flagged) {
                                if (!rowData.job_role_id || rowData.job_role_id ===
                                    'Not Assigned') {
                                    // Red if job_role_id does not exist or is "Not Assigned"
                                    $(td).css('background-color', '#f02e3f');
                                    $(td).css('color', '#fff');
                                } else {
                                    // Yellow if job_role_id exists and is not "Not Assigned"
                                    $(td).css('background-color', '#fff3cd');
                                    $(td).css('color', '#000');
                                }
                            }
                        }
                    },
                    {
                        data: 'actions',
                        title: 'Actions',
                        width: '12.5%',
                        orderable: false,
                        searchable: false
                    }
                ],
                // rowCallback: function(row, data) {
                //     // Flagged coloring
                //     if (data.flagged) {
                //         if (!data.job_role_id || data.job_role_id === 'Not Assigned') {
                //             // Red if job_role_id does not exist or is "Not Assigned"
                //             $(row).css('background-color', '#f02e3f');
                //         } else {
                //             // Yellow if job_role_id exists and is not "Not Assigned"
                //             $(row).css('background-color', '#fff3cd');
                //         }
                //     }
                // }
            });

            // Fetch master data and initialize the page
            $.ajax({
                url: '/storage/master_data.json',
                dataType: 'json',
                success: function(data) {
                    masterData = data;

                    // Populate company dropdown
                    // populateDropdown('#companyDropdown', data, 'company_id', 'company_name');
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
                    populateDropdown('#kompartemenDropdown', companyData.kompartemen, 'kompartemen_id',
                        'nama');

                    // Populate departemen_without_kompartemen
                    populateDropdown('#departemenDropdown', companyData.departemen_without_kompartemen,
                        'departemen_id', 'nama');
                }

                loadJobRoles();
            });

            // Handle kompartemen dropdown change
            $('#kompartemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $(this).val();

                resetDropdowns(['#departemenDropdown']);
                let companyData = masterData.find(c => c.company_id == companyId);
                let kompartemenData = companyData?.kompartemen.find(k => k.kompartemen_id == kompartemenId);

                if (kompartemenData?.departemen.length) {
                    // Populate departemen dropdown based on selected kompartemen
                    populateDropdown('#departemenDropdown', kompartemenData.departemen, 'kompartemen_id',
                        'nama');
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
                console.log(kompartemenId);
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
                    items.sort((a, b) => a[textField].localeCompare(b[textField])).forEach(item => {
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

            $(document).on('click', '.flagged-job-role', function(e) {
                e.preventDefault();
                const jobRoleId = $(this).data('id');
                if (!jobRoleId) return;

                // Optionally, fetch flagged info via AJAX here if needed
                $('#flagged-job-role-id').val(jobRoleId);
                $('#flaggedJobRoleModal').modal('show');
                $('#flagged-job-role-actions').removeClass('d-none');
            });

            $('#flaggedJobRoleForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('job-roles.update-flagged-status') }}",
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#flaggedJobRoleModal').modal('hide');
                            loadJobRoles(); // Refresh table
                            alert(response.message);
                        } else {
                            alert(response.message || 'Failed to update flagged status.');
                        }
                    },
                    error: function(xhr) {
                        alert('Failed to update flagged status.');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });

            $('#downloadFlaggedBtn').on('click', function(e) {
                e.preventDefault();
                const company = $('#companyDropdown').val() || '';
                const url = new URL("{{ route('job-roles.export-flagged') }}", window.location.origin);
                if (company) url.searchParams.set('company_code', company);
                window.location.href = url.toString();
            });
        });
    </script>
@endsection
