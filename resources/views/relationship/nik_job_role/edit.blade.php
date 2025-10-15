@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit User Job Role</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('nik-job.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading">Error(s) occurred:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Success Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('nik-job.update', $nikJobRole->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Periode Dropdown -->
                    <div class="mb-3">
                        <label for="periode_id" class="form-label">Periode <span class="text-danger">*</span></label>
                        <select name="periode_id" id="periode_id" class="form-control form-select" required>
                            <option value="">Select Periode</option>
                            @foreach ($periodes as $periode)
                                <option value="{{ $periode->id }}"
                                    {{ old('periode_id', $nikJobRole->periode_id) == $periode->id ? 'selected' : '' }}>
                                    {{ $periode->definisi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Company Dropdown -->
                            <div class="form-group mb-3">
                                <label for="companyDropdown">Pilih Perusahaan <span class="text-danger">*</span></label>
                                @php
                                    $selectedCompanyId = old(
                                        'company_id',
                                        data_get($nikJobRole, 'jobRole.company_id') ?: $userCompany,
                                    );
                                @endphp
                                <select id="companyDropdown" class="form-control" name="company_id" required>
                                    <option value="">-- Pilih Perusahaan --</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->company_code }}"
                                            {{ $selectedCompanyId == $company->company_code ? 'selected' : '' }}>
                                            {{ $company->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Kompartemen Dropdown -->
                            <div class="form-group mb-3">
                                <label for="kompartemenDropdown">Pilih Kompartemen</label>
                                <select id="kompartemenDropdown" class="form-control" name="kompartemen_id">
                                    <option value="">-- Pilih Kompartemen --</option>
                                </select>
                            </div>

                            <!-- Departemen Dropdown -->
                            <div class="form-group mb-3">
                                <label for="departemenDropdown">Pilih Departemen</label>
                                <select id="departemenDropdown" class="form-control" name="departemen_id">
                                    <option value="">-- Pilih Departemen --</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Job Role Dropdown -->
                            <div class="mb-3">
                                <label for="job_role_id" class="form-label">Job Role <span
                                        class="text-danger">*</span></label>
                                <select name="job_role_id" id="job_role_id" class="form-control select2" required>
                                    <option value="">Select Job Role</option>
                                </select>
                            </div>

                            <!-- User Dropdown -->
                            <div class="mb-3">
                                <label for="nik" class="form-label">User <span class="text-danger">*</span></label>
                                <select name="nik" id="nik" class="form-control select2" required>
                                    <option value="">Select User</option>
                                    @foreach ($userNIKs as $user)
                                        <option value="{{ $user->user_code }}"
                                            {{ $nikJobRole->nik == $user->user_code ? 'selected' : '' }}>
                                            {{ $user->user_code }} -
                                            {{ $user->unitKerja
                                                ? $user->unitKerja->nama .
                                                    ' | ' .
                                                    ($user->unitKerja->kompartemen ? 'Kompartemen: ' . $user->unitKerja->kompartemen->nama . ' - ' : '') .
                                                    ($user->unitKerja->departemen ? 'Departemen: ' . $user->unitKerja->departemen->nama : 'Belum ada Data Karyawan')
                                                : 'Belum ada Data Karyawan' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Current Values Display -->
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Current Assignment:</h6>
                                <p class="mb-1"><strong>NIK:</strong> {{ $nikJobRole->nik }}</p>
                                <p class="mb-1"><strong>Job Role:</strong> {{ $nikJobRole->jobRole->nama ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Job Role ID:</strong> {{ $nikJobRole->job_role_id }}</p>
                                @if ($nikJobRole->jobRole)
                                    <p class="mb-0"><strong>Company:</strong>
                                        {{ $nikJobRole->jobRole->company->nama ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Kompartemen:</strong>
                                        {{ $nikJobRole->jobRole->kompartemen->nama ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Departemen:</strong>
                                        {{ $nikJobRole->jobRole->departemen->nama ?? 'N/A' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> Ubah list user menjadi terfilter sesuai kompartemen yang terpilih
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User Job Role
                        </button>
                        <a href="{{ route('nik-job.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Load master data for this page
        window.masterData = null;

        // Fetch master data before initializing dropdowns
        const defaultCompany = "{{ $selectedCompanyId }}";
        const query = defaultCompany ? `?company=${encodeURIComponent(defaultCompany)}&active_only=true` :
            '?active_only=true';

        fetch(`/api/master-data${query}`)
            .then(response => response.json())
            .then(data => {
                // Always store as array to match previous JSON shape
                window.masterData = Array.isArray(data) ? data : [data];
                initializeDropdowns();
            })
            .catch(error => {
                console.error('Error loading master data:', error);
                alert('Error loading organization data. Please refresh the page.');
            });

        function initializeDropdowns() {
            $(document).ready(function() {
                // Initialize Select2
                $('#nik').select2({
                    placeholder: 'Select User',
                    allowClear: true
                });

                $('#job_role_id').select2({
                    placeholder: 'Select Job Role',
                    allowClear: true
                });

                const $periode = $('#periode_id');
                const $company = $('#companyDropdown');
                const $nik = $('#nik');

                function lockNik() {
                    $nik.prop('disabled', true).empty().append('<option value="">Select User</option>').trigger(
                        'change');
                }

                function loadNikByPeriodeCompany(preselect = null) {
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
                            $nik.prop('disabled', false);
                            if (preselect) {
                                $nik.val(preselect).trigger('change');
                            } else {
                                $nik.trigger('change');
                            }
                        })
                        .catch(() => lockNik());
                }

                // Reload NIK when periode or company changes
                $periode.on('change', () => loadNikByPeriodeCompany());
                $company.on('change', () => {
                    if ($periode.val()) loadNikByPeriodeCompany(@json($nikJobRole->nik));
                });

                // Get default values from server
                const defaultCompany = "{{ $selectedCompanyId }}";
                const defaultKompartemen = "{{ $nikJobRole->jobRole->kompartemen_id ?? '' }}";
                const defaultDepartemen = "{{ $nikJobRole->jobRole->departemen_id ?? '' }}";
                const defaultJobRole = "{{ $nikJobRole->job_role_id }}";

                console.log('Default values:', {
                    company: defaultCompany,
                    kompartemen: defaultKompartemen,
                    departemen: defaultDepartemen,
                    jobRole: defaultJobRole
                });

                // Flag to prevent cascade during initial setup
                let isInitializing = true;

                // Company dropdown handler
                $('#companyDropdown').on('change', function() {
                    const companyId = $(this).val();
                    console.log('Company changed to:', companyId);

                    if (!companyId) {
                        if (!isInitializing) {
                            resetDropdowns(['#kompartemenDropdown', '#departemenDropdown', '#job_role_id']);
                        }
                        return;
                    }

                    const company = window.masterData.find(c => c.company_id === companyId);
                    if (!company) return;

                    // Populate Kompartemen dropdown
                    populateKompartemenDropdown(company.kompartemen);

                    // Only show company level roles if not initializing
                    if (!isInitializing) {
                        populateJobRolesDropdown(company.job_roles_without_relations);
                    }
                });

                // Kompartemen dropdown handler
                $('#kompartemenDropdown').on('change', function() {
                    const companyId = $('#companyDropdown').val();
                    const kompartemenId = $(this).val();
                    console.log('Kompartemen changed to:', kompartemenId);

                    if (!kompartemenId) {
                        if (!isInitializing) {
                            resetDropdowns(['#departemenDropdown', '#job_role_id']);
                        }
                        return;
                    }

                    const company = window.masterData.find(c => c.company_id === companyId);
                    const kompartemen = company?.kompartemen.find(k => k.kompartemen_id === kompartemenId);
                    if (!kompartemen) return;

                    // Populate Departemen under selected Kompartemen
                    populateDepartemenDropdown(kompartemen.departemen);

                    // Show company + kompartemen level roles if not initializing
                    if (!isInitializing) {
                        const combinedRoles = [
                            ...company.job_roles_without_relations,
                            ...kompartemen.job_roles
                        ];
                        populateJobRolesDropdown(combinedRoles);
                    }
                });

                // Departemen dropdown handler
                $('#departemenDropdown').on('change', function() {
                    const companyId = $('#companyDropdown').val();
                    const kompartemenId = $('#kompartemenDropdown').val();
                    const departemenId = $(this).val();
                    console.log('Departemen changed to:', departemenId);

                    if (!departemenId && !isInitializing) {
                        // Reset job roles to kompartemen level
                        const company = window.masterData.find(c => c.company_id === companyId);
                        const kompartemen = company?.kompartemen.find(k => k.kompartemen_id ===
                            kompartemenId);
                        if (kompartemen) {
                            const combinedRoles = [
                                ...company.job_roles_without_relations,
                                ...kompartemen.job_roles
                            ];
                            populateJobRolesDropdown(combinedRoles);
                        }
                        return;
                    }

                    if (!departemenId) return;

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

                    // All levels combined with proper grouping if not initializing
                    if (!isInitializing) {
                        const combinedRoles = [
                            ...company.job_roles_without_relations,
                            ...(kompartemenId ? company.kompartemen.find(k => k.kompartemen_id ===
                                kompartemenId)?.job_roles || [] : []),
                            ...departemen.job_roles
                        ];
                        populateJobRolesDropdown(combinedRoles);
                    }
                });

                // Helper functions
                function populateKompartemenDropdown(kompartemenList) {
                    const dropdown = $('#kompartemenDropdown');
                    dropdown.empty().append('<option value="">-- Select Kompartemen --</option>');

                    if (kompartemenList?.length) {
                        dropdown.prop('disabled', false);
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
                    console.log('Populating Job Roles:', jobRoles);

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

                    const company = window.masterData.find(c => c.company_id === companyId);
                    if (!company) return;

                    // Group based on source location in JSON structure
                    jobRoles.forEach(role => {
                        if (role.status !== 'Active') return;

                        if (company.job_roles_without_relations.some(r => r.id === role.id)) {
                            groups.company.roles.push(role);
                        } else if (currentKompartemenId) {
                            const kompartemen = company.kompartemen.find(k => k.kompartemen_id ===
                                currentKompartemenId);
                            if (kompartemen?.job_roles.some(r => r.id === role.id)) {
                                groups.kompartemen.roles.push(role);
                            } else if (currentDepartemenId) {
                                const departemen = kompartemen?.departemen.find(d => d.departemen_id ===
                                    currentDepartemenId);
                                if (departemen?.job_roles.some(r => r.id === role.id)) {
                                    groups.departemen.roles.push(role);
                                }
                            }
                        }
                    });

                    // Add optgroups and options
                    Object.values(groups).forEach(group => {
                        if (group.roles.length > 0) {
                            const optgroup = $('<optgroup>', {
                                label: group.label
                            });

                            group.roles.forEach(role => {
                                optgroup.append($('<option>', {
                                    value: role.job_role_id,
                                    text: (role.job_role_id ? '' + role
                                            .job_role_id + ' - ' : 'NULL - ') + role
                                        .nama
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

                // Initialize with default values
                function setInitialValues() {
                    // Set company first
                    if (defaultCompany) {
                        $('#companyDropdown').val(defaultCompany).trigger('change');
                    }

                    // Wait for company data to load, then set kompartemen
                    setTimeout(() => {
                        if (defaultKompartemen) {
                            $('#kompartemenDropdown').val(defaultKompartemen).trigger('change');
                        }

                        // Wait for kompartemen data to load, then set departemen
                        setTimeout(() => {
                            if (defaultDepartemen) {
                                $('#departemenDropdown').val(defaultDepartemen).trigger('change');
                            }

                            // Wait for departemen data to load, then populate and set job roles
                            setTimeout(() => {
                                // Now populate job roles with all appropriate levels
                                const companyId = $('#companyDropdown').val();
                                const kompartemenId = $('#kompartemenDropdown').val();
                                const departemenId = $('#departemenDropdown').val();

                                const company = window.masterData.find(c => c.company_id ===
                                    companyId);
                                if (company) {
                                    let combinedRoles = [...company
                                        .job_roles_without_relations
                                    ];

                                    if (kompartemenId) {
                                        const kompartemen = company.kompartemen.find(k => k
                                            .kompartemen_id === kompartemenId);
                                        if (kompartemen) {
                                            combinedRoles = [...combinedRoles, ...
                                                kompartemen.job_roles
                                            ];

                                            if (departemenId) {
                                                const departemen = kompartemen.departemen
                                                    .find(d => d.departemen_id ===
                                                        departemenId);
                                                if (departemen) {
                                                    combinedRoles = [...combinedRoles, ...
                                                        departemen.job_roles
                                                    ];
                                                }
                                            }
                                        }
                                    } else if (departemenId) {
                                        const departemen = company
                                            .departemen_without_kompartemen.find(d => d
                                                .departemen_id === departemenId);
                                        if (departemen) {
                                            combinedRoles = [...combinedRoles, ...departemen
                                                .job_roles
                                            ];
                                        }
                                    }

                                    populateJobRolesDropdown(combinedRoles);

                                    // Set the job role value
                                    setTimeout(() => {
                                        if (defaultJobRole) {
                                            console.log('Setting job role to:',
                                                defaultJobRole);
                                            $('#job_role_id').val(defaultJobRole)
                                                .trigger('change');
                                        }

                                        // Mark initialization as complete
                                        isInitializing = false;
                                    }, 100);
                                }
                            }, 300);
                        }, 300);
                    }, 300);
                }

                // Start the initialization process
                setInitialValues();
            });
        }
    </script>
@endsection
