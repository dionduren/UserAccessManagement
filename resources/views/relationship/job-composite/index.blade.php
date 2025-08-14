@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="alert alert-success">
                            <h4>Success:</h4>
                            {{ session('success') }}
                        </div>
                    @endif

                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Relationship Job Roles - Composite Roles</h2>
            </div>
            <div class="card-body">

                {{-- <button class="btn btn-primary mb-3" id="createCompositeRole">Create New Relationship</button> --}}
                <a href="{{ route('job-composite.create') }}" class="btn btn-primary mb-3">
                    <i class="bi bi-plus"></i> Buat Relationship Baru
                </a>

                <!-- Dropdowns for Filtering -->
                <div class="form-group">
                    <label for="companyDropdown">Perusahaan</label>
                    <select id="companyDropdown" class="form-control select2">
                        <option value="">-- Pilih Perusahaan --</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="kompartemenDropdown">Kompartemen</label>
                    <select id="kompartemenDropdown" class="form-control select2" disabled>
                        <option value="">-- Pilih Kompartemen --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="departemenDropdown">Departemen</label>
                    <select id="departemenDropdown" class="form-control select2" disabled>
                        <option value="">-- Pilih Departemen --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jobRoleDropdown">Job Role</label>
                    <select id="jobRoleDropdown" class="form-control select2" disabled>
                        <option value="">-- Pilih Job Role --</option>
                    </select>
                </div>

                <hr>

                <h3 class="my-2 mt-3">Job Roles - Composite Roles</h3>

                <!-- DataTable -->
                <table id="composite_roles_table" class="table table-bordered table-striped table-hover cell-border mt-3">
                    <thead>
                        <tr>
                            <th>
                                <input type="text" id="search_company" class="form-control" placeholder="Search Company">
                            </th>
                            <th>
                                <input type="text" id="search_job_role" class="form-control"
                                    placeholder="Search Job Role">
                            </th>
                            <th>
                                <input type="text" id="search_composite_role" class="form-control"
                                    placeholder="Search Composite Role">
                            </th>
                            <th rowspan="2">Actions</th>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <th>Job Role</th>
                            <th>Composite Role</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
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
            let masterData = {};

            $('#kompartemenDropdown').select2();
            $('#departemenDropdown').select2();

            let compositeRolesTable = $('#composite_roles_table').DataTable({
                processing: true,
                serverSide: true,
                orderCellsTop: true,
                ajax: {
                    url: '/relationship/job-composite/data',
                    data: function(d) {
                        d.search_company = $('#search_company').val();
                        d.search_job_role = $('#search_job_role').val();
                        d.search_composite_role = $('#search_composite_role').val();

                        // dropdown filters
                        d.filter_company = $('#companyDropdown').val();
                        d.filter_kompartemen = $('#kompartemenDropdown').val();
                        d.filter_departemen = $('#departemenDropdown').val();
                        d.filter_job_role = $('#jobRoleDropdown').val();
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
                searching: false,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
            });

            // Text search inputs
            $('#search_company, #search_job_role, #search_composite_role').on('keyup', function() {
                compositeRolesTable.draw();
            });

            // Load master_data.json and normalize by company_code
            const userCompanyCode = @json($userCompanyCode);
            $.ajax({
                url: '/storage/master_data.json',
                dataType: 'json',
                success: function(data) {
                    let working = data || [];

                    if (String(userCompanyCode) !== 'A000') {
                        working = working.filter(c => String(c.company_id) === String(
                            userCompanyCode));
                    }

                    masterData = {};
                    working.forEach(c => {
                        masterData[c.company_id] = c; // key by company_id
                    });

                    // If not super user and exactly one company preselect
                    if (String(userCompanyCode) !== 'A000' && working.length === 1) {
                        $('#companyDropdown').val(working[0].company_id).trigger('change');
                    }
                },
                error: function() {
                    console.warn('Cannot load master_data.json');
                }
            });

            // On Company change -> fill kompartemen & departemen (both sources)
            $('#companyDropdown').on('change', function() {
                const code = $(this).val();
                resetDropdowns(['#kompartemenDropdown', '#departemenDropdown', '#jobRoleDropdown']);

                if (!code || !masterData[code]) {
                    compositeRolesTable.draw();
                    return;
                }
                const comp = masterData[code];

                // Kompartemen
                populateDropdown('#kompartemenDropdown', comp.kompartemen || [], 'kompartemen_id', 'nama');

                // Departemen: union of departemen_without_kompartemen + all departemen from kompartemen
                let departemenAll = [];
                if (Array.isArray(comp.departemen_without_kompartemen)) {
                    departemenAll = departemenAll.concat(comp.departemen_without_kompartemen);
                }
                // (comp.kompartemen || []).forEach(k => {
                //     if (Array.isArray(k.departemen)) {
                //         departemenAll = departemenAll.concat(k.departemen);
                //         console.log('Departemen from Kompartemen:', k.departemen);
                //     }
                // });

                // de-dup by id
                const seen = {};
                departemenAll = departemenAll.filter(d => {
                    if (seen[d.departemen_id]) {
                        return false;
                    }
                    seen[d.departemen_id] = true;
                    return true;
                });
                populateDropdown('#departemenDropdown', departemenAll, 'departemen_id', 'nama');

                compositeRolesTable.draw();
            });

            // On Kompartemen change -> filter departemen and (NEW) populate job roles that belong directly to kompartemen
            $('#kompartemenDropdown').on('change', function() {
                const code = $('#companyDropdown').val();
                const kompId = $(this).val();

                resetDropdowns(['#departemenDropdown', '#jobRoleDropdown']);

                if (!code || !masterData[code]) {
                    compositeRolesTable.draw();
                    return;
                }
                const comp = masterData[code];

                if (kompId) {
                    const komp = (comp.kompartemen || []).find(k => String(k.kompartemen_id) === String(
                        kompId));

                    // Populate departemen under this kompartemen
                    populateDropdown(
                        '#departemenDropdown',
                        (komp && Array.isArray(komp.departemen)) ? komp.departemen : [],
                        'departemen_id',
                        'nama'
                    );

                    // NEW: Populate job roles that are attached directly to the kompartemen (not via departemen)
                    let kompartemenJobRoles = [];
                    if (komp) {
                        if (Array.isArray(komp.job_roles)) {
                            kompartemenJobRoles = komp.job_roles; // e.g. structure provides job_roles
                        } else if (Array.isArray(komp.job_roles_without_departemen)) {
                            kompartemenJobRoles = komp.job_roles_without_departemen; // alternate naming
                        }
                    }

                    if (kompartemenJobRoles.length) {
                        populateDropdown('#jobRoleDropdown', kompartemenJobRoles, 'id', 'nama');
                    } else {
                        // keep disabled if none
                        $('#jobRoleDropdown').prop('disabled', true);
                    }
                } else {
                    // repopulate union when kompartemen cleared
                    $('#companyDropdown').trigger('change');
                    return;
                }
                compositeRolesTable.draw();
            });

            // On Departemen change -> job roles
            $('#departemenDropdown').on('change', function() {
                const code = $('#companyDropdown').val();
                const kompId = $('#kompartemenDropdown').val();
                const depId = $(this).val();

                resetDropdowns(['#jobRoleDropdown']);

                if (!code || !masterData[code]) {
                    compositeRolesTable.draw();
                    return;
                }
                const comp = masterData[code];
                let jobRoles = [];

                if (depId) {
                    // locate departemen under selected kompartemen OR departemen_without_kompartemen
                    let departemenNode = null;
                    if (kompId) {
                        const komp = (comp.kompartemen || []).find(k => String(k.kompartemen_id) === String(
                            kompId));
                        departemenNode = komp?.departemen?.find(d => String(d.departemen_id) === String(
                            depId));
                    } else {
                        departemenNode = (comp.departemen_without_kompartemen || []).find(d => String(d
                            .departemen_id) === String(depId));
                        if (!departemenNode) {
                            for (const k of comp.kompartemen || []) {
                                const found = (k.departemen || []).find(d => String(d.departemen_id) ===
                                    String(depId));
                                if (found) {
                                    departemenNode = found;
                                    break;
                                }
                            }
                        }
                    }
                    if (departemenNode?.job_roles) jobRoles = departemenNode.job_roles;
                } else {
                    // no departemen chosen: allow company-level unassigned job roles list if present
                    jobRoles = comp.job_roles_without_relations || [];
                }

                if (depId) {
                    populateDropdown('#jobRoleDropdown', jobRoles, 'id', 'nama');
                } else {
                    $('#kompartemenDropdown').trigger('change');
                }
                compositeRolesTable.draw();
            });

            // On Job Role change -> redraw
            $('#jobRoleDropdown').on('change', function() {
                compositeRolesTable.draw();
            });

            function populateDropdown(selector, items, valueField, textField) {
                const el = $(selector);
                el.empty().append('<option value="">-- Select --</option>');
                if (Array.isArray(items) && items.length) {
                    items.forEach(it => {
                        el.append(`<option value="${it[valueField]}">${it[textField]}</option>`);
                    });
                    el.prop('disabled', false);
                } else {
                    el.prop('disabled', true);
                }
            }

            function resetDropdowns(list) {
                list.forEach(sel => {
                    $(sel).empty().append('<option value="">-- Select --</option>').prop('disabled', true);
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
