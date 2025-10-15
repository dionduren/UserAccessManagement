@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Create User Job Role</h1>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('nik-job.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="periode_id" class="form-label">Periode</label>
                <select name="periode_id" id="periode_id" class="form-control form-select" required>
                    <option value="">Select Periode</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}" {{ old('periode_id') == $periode->id ? 'selected' : '' }}>
                            {{ $periode->definisi }}
                        </option>
                    @endforeach
                </select>
            </div>

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
                <select id="kompartemenDropdown" class="form-control">
                    <option value="">-- Semua Kompartemen --</option>
                </select>
            </div>

            <!-- Dropdown for Departemen Selection -->
            <div class="form-group mb-3">
                <label for="departemenDropdown">Pilih Departemen</label>
                <select id="departemenDropdown" class="form-control">
                    <option value="">-- Semua Departemen --</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="job_role_id" class="form-label">Job Role</label>
                <select name="job_role_id" id="job_role_id" class="form-control select2" required>
                    <option value="">Select Job Role</option>
                    <!-- Options will be dynamically populated based on selection -->
                </select>
            </div>

            <div class="mb-3">
                <label for="nik" class="form-label">User</label>
                <select name="nik" id="nik" class="form-control select2" required disabled>
                    <option value="">Select User</option>
                </select>
            </div>

            <div>
                <span class="text-danger">Note: Ubah list user menjadi terfilter sesuai kompartemen yang terpilih</span>
            </div>

            <button type="submit" class="btn btn-primary">Create User Job Role</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Do NOT use master_data.json. Fetch per company like edit page.

        window.masterData = null;

        function fetchCompanyMaster(companyId) {
            if (!companyId) {
                window.masterData = null;
                return Promise.resolve(null);
            }
            const qs = `?company=${encodeURIComponent(companyId)}&active_only=true`;
            return fetch(`/api/master-data${qs}`)
                .then(r => r.json())
                .then(data => {
                    window.masterData = Array.isArray(data) ? data : [data];
                    return window.masterData;
                });
        }

        $(document).ready(function() {
            const $nik = $('#nik');
            const $periode = $('#periode_id');
            const $company = $('#companyDropdown');

            // init select2
            $nik.select2({
                placeholder: 'Select User',
                allowClear: true
            });

            function lockNik() {
                $nik.prop('disabled', true).empty().append('<option value="">Select User</option>').trigger(
                    'change');
            }

            function loadNikByPeriodeCompany() {
                const pid = $periode.val();
                if (!pid) {
                    lockNik();
                    return;
                }
                const company = $company.val();

                const params = new URLSearchParams({
                    periode_id: pid
                });
                if (company) params.append('company', company);

                fetch(`{{ route('nik-job.users-by-periode') }}?` + params.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(items => {
                        $nik.empty().append('<option value=""></option>');
                        items.forEach(it => $nik.append(new Option(it.text, it.id)));
                        $nik.prop('disabled', false).trigger('change');
                    })
                    .catch(() => lockNik());
            }

            // Periode gate
            $periode.on('change', loadNikByPeriodeCompany);

            // Company filter
            $company.on('change', function() {
                // Only reload if periode already selected
                if ($periode.val()) loadNikByPeriodeCompany();
            });

            // Initial state
            if ($periode.val()) loadNikByPeriodeCompany();
            else lockNik();

            // Company -> fetch master data, then populate cascades as before
            $('#companyDropdown').on('change', async function() {
                const companyId = $(this).val();
                // fetch company-scoped master first
                await fetchCompanyMaster(companyId);
                // then your existing populate logic
                if (!companyId) {
                    resetDropdowns(['#kompartemenDropdown', '#departemenDropdown', '#job_role_id']);
                    return;
                }
                const company = window.masterData?.find(c => c.company_id === companyId);
                if (!company) return;
                populateKompartemenDropdown(company.kompartemen);
                populateJobRolesDropdown(company.job_roles_without_relations);
            });

            // Kompartemen dropdown handler  
            $('#kompartemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $(this).val();

                if (!kompartemenId) {
                    resetDropdowns(['#departemenDropdown', '#job_role_id']);
                    return;
                }

                const company = window.masterData.find(c => c.company_id === companyId);
                const kompartemen = company?.kompartemen.find(k => k.kompartemen_id === kompartemenId);
                if (!kompartemen) return;

                // Populate Departemen under selected Kompartemen
                populateDepartemenDropdown(kompartemen.departemen);

                // Show company + kompartemen level roles
                const combinedRoles = [
                    ...company.job_roles_without_relations,
                    ...kompartemen.job_roles
                ];
                populateJobRolesDropdown(combinedRoles);
            });

            // Departemen dropdown handler
            $('#departemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $('#kompartemenDropdown').val();
                const departemenId = $(this).val();

                const company = window.masterData.find(c => c.company_id === companyId);
                let departemen;

                if (kompartemenId) {
                    const kompartemen = company?.kompartemen.find(k => k.kompartemen_id ===
                        kompartemenId);
                    departemen = kompartemen?.departemen.find(d => d.departemen_id === departemenId);
                } else {
                    departemen = company?.departemen_without_kompartemen.find(d => d.departemen_id ===
                        departemenId);
                }

                if (!departemen) return;

                // All levels combined with proper grouping
                const combinedRoles = [
                    ...company.job_roles_without_relations, // Company level
                    ...(kompartemenId ? company.kompartemen.find(k => k.kompartemen_id ===
                        kompartemenId)?.job_roles || [] : []), // Kompartemen level
                    ...departemen.job_roles // Departemen level
                ];

                populateJobRolesDropdown(combinedRoles);
            });

            // Helper functions
            function populateKompartemenDropdown(kompartemenList) {
                const dropdown = $('#kompartemenDropdown');
                dropdown.empty().append('<option value="">-- Select Kompartemen --</option>');

                if (kompartemenList?.length) {
                    dropdown.prop('disabled', false);
                    // Sort kompartemenList by nama
                    const sortedList = [...kompartemenList].sort((a, b) => a.nama.localeCompare(b.nama));
                    sortedList.forEach(item => {
                        dropdown.append(`<option value="${item.kompartemen_id}">${item.nama}</option>`);
                    });
                } else {
                    dropdown.prop('disabled', true);
                }
            }

            function populateDepartemenDropdown(departemenList) {
                const dropdown = $('#departemenDropdown');
                dropdown.empty().append('<option value="">-- Select Departemen --</option>');

                if (departemenList?.length) {
                    dropdown.prop('disabled', false);
                    // Sort departemenList by nama
                    const sortedList = [...departemenList].sort((a, b) => a.nama.localeCompare(b.nama));
                    sortedList.forEach(item => {
                        dropdown.append(`<option value="${item.departemen_id}">${item.nama}</option>`);
                    });
                } else {
                    dropdown.prop('disabled', true);
                }
            }

            function populateJobRolesDropdown(jobRoles) {
                const dropdown = $('#job_role_id');
                dropdown.empty().append('<option value="">-- Select Job Role --</option>');

                if (!jobRoles?.length) {
                    dropdown.prop('disabled', true);
                    return;
                }

                // Create optgroup structure 
                const groups = {
                    company: {
                        label: 'Company Level Job Roles',
                        roles: []
                    },
                    kompartemen: {
                        label: 'Kompartemen Level Job Roles',
                        roles: []
                    },
                    departemen: {
                        label: 'Departemen Level Job Roles',
                        roles: []
                    }
                };

                const currentKompartemenId = $('#kompartemenDropdown').val();
                const currentDepartemenId = $('#departemenDropdown').val();
                const companyId = $('#companyDropdown').val();

                // Find current company
                const company = window.masterData.find(c => c.company_id === companyId);
                if (!company) return;

                // Group based on source location in JSON structure
                jobRoles.forEach(role => {

                    if (role.status !== 'Active') return;

                    // Check if role exists in company's direct roles
                    if (company.job_roles_without_relations.some(r => r.id === role.id)) {
                        groups.company.roles.push(role);
                        return;
                    }

                    // Check if role exists in current kompartemen
                    if (currentKompartemenId) {
                        const kompartemen = company.kompartemen.find(k => k.kompartemen_id ===
                            currentKompartemenId);
                        if (kompartemen?.job_roles.some(r => r.id === role.id)) {
                            groups.kompartemen.roles.push(role);
                            return;
                        }

                        // Check if role exists in current departemen
                        if (currentDepartemenId) {
                            const departemen = kompartemen.departemen.find(d => d.departemen_id ===
                                currentDepartemenId);
                            if (departemen?.job_roles.some(r => r.id === role.id)) {
                                groups.departemen.roles.push(role);
                                return;
                            }
                        }
                    }
                });

                // Add optgroups and their options
                Object.values(groups).forEach(group => {
                    if (group.roles.length > 0) {
                        const optgroup = $('<optgroup>', {
                            label: group.label
                        });

                        group.roles.forEach(role => {
                            optgroup.append($('<option>', {
                                value: role.job_role_id,
                                text: role.nama
                            }));
                        });

                        dropdown.append(optgroup);
                    }
                });

                dropdown.prop('disabled', false);
            }

            function resetDropdowns(selectors) {
                selectors.forEach(selector => {
                    $(selector)
                        .empty()
                        .append('<option value="">-- Select --</option>')
                        .prop('disabled', true);
                });
            }
        });
    </script>
@endsection
