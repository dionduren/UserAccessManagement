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

            // Load the JSON file for dropdown data
            $.ajax({
                url: '/storage/master_data.json', // Path to the generated JSON file
                dataType: 'json',
                success: function(data) {
                    // Populate Company dropdown
                    let companyDropdown = $('#companyDropdown');
                    companyDropdown.empty().append('<option value="">-- Select Company --</option>');
                    data.forEach(company => {
                        companyDropdown.append(
                            `<option value="${company.company_id}">${company.company_name}</option>`
                        );
                    });

                    // Handle Company dropdown change
                    companyDropdown.on('change', function() {
                        let companyId = $(this).val();

                        // Reset kompartemen and departemen dropdowns
                        let kompartemenDropdown = $('#kompartemenDropdown').prop('disabled',
                            true).empty().append(
                            '<option value="">-- Select Kompartemen --</option>');
                        let departemenDropdown = $('#departemenDropdown').prop('disabled', true)
                            .empty().append(
                                '<option value="">-- Select Departemen --</option>');

                        if (companyId) {
                            // Get selected company data
                            let selectedCompany = data.find(company => company.company_id ==
                                companyId);

                            if (selectedCompany) {
                                // Populate kompartemen dropdown
                                if (selectedCompany.kompartemen && selectedCompany.kompartemen
                                    .length > 0) {
                                    kompartemenDropdown.prop('disabled', false);
                                    selectedCompany.kompartemen.forEach(kompartemen => {
                                        kompartemenDropdown.append(
                                            `<option value="${kompartemen.id}">${kompartemen.name}</option>`
                                        );
                                    });
                                }

                                // Populate departemen without kompartemen
                                if (selectedCompany.departemen_without_kompartemen &&
                                    selectedCompany.departemen_without_kompartemen.length > 0) {
                                    departemenDropdown.prop('disabled', false);
                                    selectedCompany.departemen_without_kompartemen.forEach(
                                        departemen => {
                                            departemenDropdown.append(
                                                `<option value="${departemen.id}">${departemen.name}</option>`
                                            );
                                        });
                                }
                            }
                        }
                        table.ajax.reload();
                    });

                    // Handle Kompartemen dropdown change
                    $('#kompartemenDropdown').on('change', function() {
                        let kompartemenId = $(this).val();

                        // Reset departemen dropdown
                        let departemenDropdown = $('#departemenDropdown').prop('disabled', true)
                            .empty().append(
                                '<option value="">-- Select Departemen --</option>');

                        if (kompartemenId) {
                            let companyId = $('#companyDropdown').val();
                            let selectedCompany = data.find(company => company.company_id ==
                                companyId);
                            if (selectedCompany) {
                                let selectedKompartemen = selectedCompany.kompartemen.find(
                                    kompartemen => kompartemen.id == kompartemenId);

                                // Populate departemen under selected kompartemen
                                if (selectedKompartemen && selectedKompartemen.departemen &&
                                    selectedKompartemen.departemen.length > 0) {
                                    departemenDropdown.prop('disabled', false);
                                    selectedKompartemen.departemen.forEach(departemen => {
                                        departemenDropdown.append(
                                            `<option value="${departemen.id}">${departemen.name}</option>`
                                        );
                                    });
                                }
                            }
                        }
                        table.ajax.reload();
                    });

                    $('#departemenDropdown').on('change', function() {
                        table.ajax.reload();
                    });
                },
                error: function() {
                    alert('Failed to load company data.');
                }
            });

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
                ],
                serverMethod: 'GET',
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    paginate: {
                        next: 'Next',
                        previous: 'Previous'
                    }
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
