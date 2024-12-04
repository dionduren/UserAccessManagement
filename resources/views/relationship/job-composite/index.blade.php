@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Relationship > Job Roles - Composite Roles</h1>

        {{-- <button class="btn btn-primary mb-3" id="createCompositeRole">Create New Relationship</button> --}}
        <a href="{{ route('job-composite.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus"></i> Create New Relationship
        </a>

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

        <hr>

        <h3 class="my-2 mt-3">Job Roles - Composite Roles</h3>

        <!-- DataTable -->
        <table id="composite_roles_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Job Role</th>
                    <th>Composite Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="CompositeRoleModal" tabindex="-1" aria-labelledby="CompositeRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="CompositeRoleModalLabel">Composite Role Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-composite-role-details">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let masterData = {}; // Store parsed JSON for efficient lookups

            let compositeRolesTable = $('#composite_roles_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/relationship/job-composite/data',
                    data: function(d) {
                        d.company_id = $('#companyDropdown').val();
                        d.kompartemen_id = $('#kompartemenDropdown').val();
                        d.departemen_id = $('#departemenDropdown').val();
                        d.job_role_id = $('#jobRoleDropdown').val();
                    },
                },
                columns: [{
                        data: 'company',
                        name: 'company'
                    },
                    {
                        data: 'job_role',
                        name: 'job_role'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
            });


            // Fetch JSON data
            $.ajax({
                url: '/storage/master_data.json',
                dataType: 'json',
                success: function(data) {
                    masterData = data.reduce((acc, company) => {
                        acc[company.company_id] = company;
                        return acc;
                    }, {});
                    // populateDropdown('#companyDropdown', masterData, 'company_id', 'company_name');
                    populateDropdown('#companyDropdown', Object.values(masterData), 'company_id',
                        'company_name');

                },
                error: function() {
                    alert('Failed to load master data.');
                }
            });

            // Handle Company dropdown change
            $('#companyDropdown').on('change', function() {
                const companyId = $(this).val();
                const selectedCompany = masterData[companyId];
                resetDropdowns(['#kompartemenDropdown', '#departemenDropdown', '#jobRoleDropdown']);

                if (selectedCompany) {
                    populateDropdown('#kompartemenDropdown', selectedCompany.kompartemen, 'id', 'name');
                    populateDropdown('#departemenDropdown', selectedCompany.departemen_without_kompartemen,
                        'id', 'name');
                }
            });

            // Handle Kompartemen dropdown change
            $('#kompartemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $(this).val();
                const selectedCompany = masterData[companyId];
                const kompartemen = selectedCompany.kompartemen.find((k) => k.id == kompartemenId);

                resetDropdowns(['#departemenDropdown', '#jobRoleDropdown']);
                if (kompartemen) {
                    populateDropdown('#departemenDropdown', kompartemen.departemen, 'id', 'name');
                }
            });

            // Handle Departemen dropdown change
            $('#departemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const departemenId = $(this).val();
                const kompartemenId = $('#kompartemenDropdown').val();
                const selectedCompany = masterData[companyId];
                const jobRoles = [];

                if (departemenId) {
                    let selectedDepartemen;

                    // Check if departemen belongs to a kompartemen
                    if (kompartemenId) {
                        const selectedKompartemen = selectedCompany.kompartemen?.find((k) => k.id ==
                            kompartemenId);
                        selectedDepartemen = selectedKompartemen?.departemen?.find((d) => d.id ==
                            departemenId);
                    } else {
                        // Fallback to departemen_without_kompartemen
                        selectedDepartemen = selectedCompany.departemen_without_kompartemen?.find((d) => d
                            .id == departemenId);
                    }

                    if (selectedDepartemen && selectedDepartemen.job_roles) {
                        jobRoles.push(...selectedDepartemen.job_roles);
                    }
                } else {
                    // Populate job_roles_without_relations if no departemen is selected
                    jobRoles.push(...selectedCompany.job_roles_without_relations || []);
                }

                console.log('Job Roles:', jobRoles);

                // Populate Job Roles Dropdown
                populateDropdown('#jobRoleDropdown', jobRoles, 'id', 'name');
            });



            function reloadTable() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $('#kompartemenDropdown').val();
                const departemenId = $('#departemenDropdown').val();
                const jobRoleId = $('#jobRoleDropdown').val();

                compositeRolesTable.ajax.reload(null, false); // Reloads the data without resetting pagination
            }



            // Reload DataTable on dropdown changes
            $('#companyDropdown, #kompartemenDropdown, #departemenDropdown, #jobRoleDropdown').on('change',
                reloadTable);


            function populateDropdown(selector, items, valueField, textField) {
                const dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');

                if (Array.isArray(items) && items.length) {
                    items.forEach((item) => {
                        dropdown.append(`<option value="${item[valueField]}">${item[textField]}</option>`);
                    });
                    dropdown.prop('disabled', false);
                } else {
                    dropdown.prop('disabled', true);
                }
            }

            function resetDropdowns(selectors) {
                selectors.forEach((selector) => {
                    $(selector).empty().append('<option value="">-- Select --</option>').prop('disabled',
                        true);
                });
            }



            // Show modal for composite role details
            $(document).on('click', '.show-composite-role', function(e) {
                e.preventDefault();
                const compositeRoleId = $(this).data('id');

                $.ajax({
                    url: `/relationship/job-composite/${compositeRoleId}`,
                    method: 'GET',
                    success: function(response) {
                        $('#modal-composite-role-details').html(response);
                        $('#CompositeRoleModal').modal('show');
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
                $('#CompositeRoleModal').modal('hide');
            });
        });
    </script>
@endsection
