@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Edit User Job Role</h1>

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

        <form action="{{ route('nik-job.update', $nikJobRole->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Periode Dropdown -->
            <div class="mb-3">
                <label for="periode_id" class="form-label">Periode</label>
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

            <!-- User Dropdown -->
            <div class="mb-3">
                <label for="nik" class="form-label">User</label>
                <select name="nik" id="nik" class="form-control select2" required>
                    <option value="">Select User</option>
                    @foreach ($userNIKs as $user)
                        <option value="{{ $user->user_code }}" {{ $nikJobRole->nik == $user->user_code ? 'selected' : '' }}>
                            {{ $user->user_code }} -
                            {{ $user->userDetail ? $user->userDetail->nama : 'Belum ada Data Karyawan' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cascading Dropdowns for Job Role selection -->
            <!-- Company Dropdown -->
            <div class="form-group mb-3">
                <label for="companyDropdown">Pilih Perusahaan</label>
                <select id="companyDropdown" class="form-control" name="company_id" required>
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->company_code }}"
                            {{ old('company_id', $nikJobRole->jobRole->company_id) == $company->company_code ? 'selected' : '' }}>
                            {{ $company->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Kompartemen Dropdown -->
            <div class="form-group mb-3">
                <label for="kompartemenDropdown">Pilih Kompartemen</label>
                <select id="kompartemenDropdown" class="form-control" name="kompartemen_id" disabled>
                    <option value="">-- Pilih Kompartemen --</option>
                </select>
            </div>

            <!-- Departemen Dropdown -->
            <div class="form-group mb-3">
                <label for="departemenDropdown">Pilih Departemen</label>
                <select id="departemenDropdown" class="form-control" name="departemen_id" disabled>
                    <option value="">-- Pilih Departemen --</option>
                </select>
            </div>

            <!-- Job Role Dropdown -->
            <div class="mb-3">
                <label for="job_role_id" class="form-label">Job Role</label>
                <select name="job_role_id" id="job_role_id" class="form-control select2" required>
                    <option value="">Select Job Role</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update User Job Role</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#nik').select2({
                placeholder: 'Select User',
                allowClear: true
            });
            $('#job_role_id').select2({
                placeholder: 'Select Job Role',
                allowClear: true
            });

            // Default values passed from the controller (if available)
            var defaultCompany = "{{ $nikJobRole->jobRole->company_id ?? '' }}";
            var defaultKompartemen = "{{ $nikJobRole->jobRole->kompartemen_id ?? '' }}";
            var defaultDepartemen = "{{ $nikJobRole->jobRole->departemen_id ?? '' }}";
            var defaultJobRole = "{{ $nikJobRole->job_role_id }}";

            // Preload cascading dropdowns based on default values
            if (defaultCompany) {
                loadKompartemen(defaultCompany, function() {
                    $('#companyDropdown').val(defaultCompany).trigger('change');
                    if (defaultKompartemen) {
                        loadDepartemen(defaultKompartemen, function() {
                            $('#kompartemenDropdown').val(defaultKompartemen).trigger('change');
                            if (defaultDepartemen) {
                                loadJobRoles(defaultCompany, defaultKompartemen, defaultDepartemen);
                            }
                        });
                    }
                });
            }

            // When Company changes, load Kompartemen and Job Roles
            $('#companyDropdown').on('change', function() {
                var companyId = $(this).val();
                resetDropdowns(['#kompartemenDropdown', '#departemenDropdown', '#job_role_id']);
                loadKompartemen(companyId);
                loadJobRoles(companyId);
            });

            // When Kompartemen changes, load Departemen and Job Roles
            $('#kompartemenDropdown').on('change', function() {
                var companyId = $('#companyDropdown').val();
                var kompartemenId = $(this).val();
                resetDropdowns(['#departemenDropdown', '#job_role_id']);
                loadDepartemen(kompartemenId);
                loadJobRoles(companyId, kompartemenId);
            });

            // When Departemen changes, load Job Roles
            $('#departemenDropdown').on('change', function() {
                var companyId = $('#companyDropdown').val();
                var kompartemenId = $('#kompartemenDropdown').val();
                var departemenId = $(this).val();
                loadJobRoles(companyId, kompartemenId, departemenId);
            });

            // Helper functions (same as in your create view)
            function loadKompartemen(companyId, callback) {
                $.ajax({
                    url: '/get-kompartemen',
                    method: 'GET',
                    data: {
                        company_id: companyId
                    },
                    success: function(data) {
                        populateDropdown('#kompartemenDropdown', data, 'id', 'name');
                        if (callback) callback();
                    },
                    error: function() {
                        alert('Failed to fetch Kompartemen.');
                    }
                });
            }

            function loadDepartemen(kompartemenId, callback) {
                $.ajax({
                    url: '/get-departemen',
                    method: 'GET',
                    data: {
                        kompartemen_id: kompartemenId
                    },
                    success: function(data) {
                        populateDropdown('#departemenDropdown', data, 'id', 'name');
                        if (callback) callback();
                    },
                    error: function() {
                        alert('Failed to fetch Departemen.');
                    }
                });
            }

            function loadJobRoles(companyId, kompartemenId = null, departemenId = null) {
                $.ajax({
                    url: '/get-job-roles',
                    method: 'GET',
                    data: {
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    success: function(data) {
                        populateDropdown('#job_role_id', data, 'id', 'job_role');
                    },
                    error: function() {
                        alert('Failed to fetch Job Roles.');
                    }
                });
            }

            function populateDropdown(selector, items, valueField, textField) {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');
                if (items && items.length > 0) {
                    dropdown.prop('disabled', false);
                    items.forEach(function(item) {
                        let selected = (item[valueField] == defaultJobRole) ? 'selected' : '';
                        dropdown.append(
                            `<option value="${item[valueField]}" ${selected}>${item[textField]}</option>`
                        );
                    });
                } else {
                    dropdown.prop('disabled', true);
                }
            }

            function resetDropdowns(selectors) {
                selectors.forEach(function(selector) {
                    $(selector)
                        .empty()
                        .append('<option value="">-- Select --</option>')
                        .prop('disabled', true);
                });
            }
        });
    </script>
@endsection
