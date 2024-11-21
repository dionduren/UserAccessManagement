@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Composite Roles</h1>

        <button class="btn btn-primary mb-3" id="createCompositeRole">Create New Composite Role</button>

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
            <tbody></tbody>
        </table>
    </div>

    <!-- Modals -->
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
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createEditCompositeRoleModal" tabindex="-1"
        aria-labelledby="createEditCompositeRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEditCompositeRoleModalLabel">Create/Edit Composite Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let masterData = {}; // Store parsed JSON for efficient lookups

            // Initialize DataTable
            let compositeRolesTable = $('#composite_roles_table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('composite-roles.data') }}",
                    data: function(d) {
                        d.company_id = $('#companyDropdown').val();
                        d.kompartemen_id = $('#kompartemenDropdown').val();
                        d.departemen_id = $('#departemenDropdown').val();
                        d.job_role_id = $('#jobRoleDropdown').val();
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to load composite roles data: ' + error);
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
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    paginate: {
                        next: 'Next',
                        previous: 'Previous'
                    }
                }
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
                let companyId = $(this).val();
                console.log('Selected Company ID:', companyId);

                let selectedCompany = masterData[companyId];
                console.log('Selected Company Data:', selectedCompany);
                if (selectedCompany) {
                    // Populate Kompartemen
                    populateDropdown('#kompartemenDropdown', selectedCompany.kompartemen, 'id', 'name');

                    // Populate Departemen Without Kompartemen
                    populateDropdown('#departemenDropdown', selectedCompany.departemen_without_kompartemen,
                        'id', 'name');
                } else {
                    resetDropdowns(['#kompartemenDropdown', '#departemenDropdown']);
                }
            });


            // Handle Kompartemen dropdown change
            $('#kompartemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $(this).val();
                resetDropdowns(['#departemenDropdown']);
                let company = masterData[companyId];
                let kompartemen = company.kompartemen.find(k => k.id == kompartemenId);
                if (kompartemen) {
                    populateDropdown('#departemenDropdown', kompartemen.departemen, 'id', 'name');
                }
                reloadTable();
            });

            // Handle Departemen Dropdown Change
            $('#departemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $('#kompartemenDropdown').val();
                const departemenId = $(this).val();

                let jobRoleDropdown = $('#jobRoleDropdown').prop('disabled', true).empty().append(
                    '<option value="">-- Select Job Role --</option>');

                if (companyId && masterData[companyId]) {
                    let selectedCompany = masterData[companyId];

                    if (departemenId) {
                        // Find the selected department
                        let selectedDepartemen;
                        if (kompartemenId) {
                            let selectedKompartemen = selectedCompany.kompartemen.find(k => k.id ==
                                kompartemenId);
                            selectedDepartemen = selectedKompartemen?.departemen.find(d => d.id ==
                                departemenId);
                        } else {
                            selectedDepartemen = selectedCompany.departemen_without_kompartemen.find(d => d
                                .id == departemenId);
                        }

                        if (selectedDepartemen) {
                            populateDropdown(jobRoleDropdown, selectedDepartemen.job_roles, 'id', 'name');
                        }
                    } else if (!kompartemenId) {
                        // Populate job roles without relations if no kompartemen or departemen selected
                        populateDropdown(jobRoleDropdown, selectedCompany.job_roles_without_relations, 'id',
                            'name');
                    }
                }
            });


            function reloadTable() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $('#kompartemenDropdown').val();
                const departemenId = $('#departemenDropdown').val();

                compositeRolesTable.ajax.url('/composite-roles/data')
                    .data({
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    })
                    .load();
            }

            // Reload DataTable on dropdown changes
            $('#companyDropdown, #kompartemenDropdown, #departemenDropdown, #jobRoleDropdown').on('change',
                function() {
                    compositeRolesTable.ajax.reload(null, false); // Reload without resetting pagination
                });

            function populateDropdown(selector, items, valueField, textField) {
                console.log('Populating dropdown:', selector, items); // Debugging
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');

                if (Array.isArray(items) && items.length > 0) {
                    items.forEach(item => {
                        if (item[valueField] && item[textField]) {
                            dropdown.append(
                                `<option value="${item[valueField]}">${item[textField]}</option>`);
                        }
                    });
                    dropdown.prop('disabled', false);
                } else {
                    console.warn(`populateDropdown received invalid or empty items for selector: ${selector}`);
                    dropdown.prop('disabled', true);
                }
            }


            function resetDropdowns(selectors) {
                selectors.forEach(selector => {
                    $(selector).empty().append('<option value="">-- Select --</option>').prop('disabled',
                        true);
                });
            }



            // Show modal for composite role details
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
