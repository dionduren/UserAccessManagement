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
                    <!-- Job Role Details will be loaded here dynamically -->
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

            // To store JSON data for efficient lookups
            let masterData = {};

            // Initalize data company, kompartemen, departemen
            $.ajax({
                url: '/storage/master_data.json', // Path to your generated JSON file
                dataType: 'json',
                success: function(data) {
                    // Initialize dropdowns with fetched data
                    let companyDropdown = $('#companyDropdown');
                    companyDropdown.empty().append('<option value="">-- Select Company --</option>');

                    data.forEach(company => {
                        companyDropdown.append(
                            `<option value="${company.company_id}">${company.company_name}</option>`
                        );
                    });

                    companyDropdown.on('change', function() {
                        let companyId = $(this).val();
                        let selectedCompany = data.find(company => company.company_id ==
                            companyId);
                        console.log(selectedCompany);
                        // Populate kompartemen dropdown
                        let kompartemenDropdown = $('#kompartemenDropdown').prop('disabled', !
                            selectedCompany).empty().append(
                            '<option value="">-- Select Kompartemen --</option>');
                        if (selectedCompany && selectedCompany.kompartemen.length > 0) {
                            selectedCompany.kompartemen.forEach(kompartemen => {
                                kompartemenDropdown.append(
                                    `<option value="${kompartemen.id}">${kompartemen.name}</option>`
                                );
                            });
                        }

                        // Handle departemen without kompartemen
                        let departemenDropdown = $('#departemenDropdown').prop('disabled', !
                            selectedCompany).empty().append(
                            '<option value="">-- Select Departemen --</option>');
                        console.log(selectedCompany.departemen_without_kompartemen);
                        if (selectedCompany && selectedCompany.departemen_without_kompartemen
                            .length > 0) {
                            selectedCompany.departemen_without_kompartemen.forEach(
                                departemen => {
                                    departemenDropdown.append(
                                        `<option value="${departemen.id}">${departemen.name}</option>`
                                    );
                                });
                        }
                    });

                    // Similar logic for kompartemen and departemen dropdown changes
                },
                error: function() {
                    alert('Failed to load company data.');
                }
            });

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

            // // Load Kompartemen based on selected company
            // $('#companyDropdown').change(function() {
            //     let companyId = $(this).val();
            //     if (companyId) {
            //         $.ajax({
            //             url: '/get-kompartemen',
            //             method: 'GET',
            //             data: {
            //                 company_id: companyId
            //             },
            //             success: function(data) {
            //                 $('#kompartemenDropdown').empty().append(
            //                     '<option value="">-- Semua Kompartemen --</option>');
            //                 $.each(data, function(key, value) {
            //                     $('#kompartemenDropdown').append('<option value="' +
            //                         value.id + '">' + value.name + '</option>');
            //                 });
            //                 $('#kompartemenDropdown').prop('disabled', false);
            //             },
            //             error: function() {
            //                 alert('Failed to fetch Kompartemen.');
            //             }
            //         });
            //     } else {
            //         $('#kompartemenDropdown').prop('disabled', true).empty().append(
            //             '<option value="">-- Semua Kompartemen --</option>');
            //         $('#departemenDropdown').prop('disabled', true).empty().append(
            //             '<option value="">-- Semua Departemen --</option>');
            //         jobRolesTable.clear().draw();
            //     }
            // });

            // // Load Departemen based on selected Kompartemen
            // $('#kompartemenDropdown').change(function() {
            //     let kompartemenId = $(this).val();
            //     if (kompartemenId) {
            //         $.ajax({
            //             url: '/get-departemen',
            //             method: 'GET',
            //             data: {
            //                 kompartemen_id: kompartemenId
            //             },
            //             success: function(data) {
            //                 $('#departemenDropdown').empty().append(
            //                     '<option value="">-- Semua Departemen --</option>');
            //                 $.each(data, function(key, value) {
            //                     $('#departemenDropdown').append('<option value="' +
            //                         value.id + '">' + value.name + '</option>');
            //                 });
            //                 $('#departemenDropdown').prop('disabled', false);
            //             },
            //             error: function() {
            //                 alert('Failed to fetch Departemen.');
            //             }
            //         });
            //     } else {
            //         $('#departemenDropdown').prop('disabled', true).empty().append(
            //             '<option value="">-- Semua Departemen --</option>');
            //         jobRolesTable.clear().draw();
            //     }
            // });

            // Load Job Roles when a dropdown changes
            $('#companyDropdown, #kompartemenDropdown, #departemenDropdown').change(loadJobRoles);

            // On change of companyDropdown
            $('#companyDropdown').on('change', function() {
                const companyId = $(this).val();
                $('#kompartemenDropdown, #departemenDropdown, #jobRoleDropdown').empty();

                if (companyId) {
                    // Load JSON data (assumes it's globally available or fetched once)
                    $.getJSON('/storage/master_data.json', function(data) {
                        const companyData = data.find(company => company.company_id == companyId);

                        // Populate Kompartemen
                        if (companyData && companyData.kompartemen) {
                            companyData.kompartemen.forEach(kompartemen => {
                                $('#kompartemenDropdown').append(
                                    `<option value="${kompartemen.id}">${kompartemen.name}</option>`
                                );
                            });
                        }

                        // Populate Departemen without Kompartemen
                        if (companyData && companyData.departemen_without_kompartemen) {
                            companyData.departemen_without_kompartemen.forEach(departemen => {
                                $('#departemenDropdown').append(
                                    `<option value="${departemen.id}">${departemen.name}</option>`
                                );
                            });
                        }
                    });
                }
            });


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
