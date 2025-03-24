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

            <div class="mb-3">
                <label for="nik" class="form-label">User</label>
                <select name="nik" id="nik" class="form-control select2" required>
                    <option value="">Select User</option>
                    @foreach ($userNIKs as $user)
                        <option value="{{ $user->user_code }}">
                            {{ $user->user_code }} -
                            {{ $user->userDetail
                                ? $user->userDetail->nama .
                                    ' | ' .
                                    ($user->userDetail->kompartemen ? 'Kompartemen: ' . $user->userDetail->kompartemen->name . ' - ' : '') .
                                    ($user->userDetail->departemen
                                        ? 'Departemen: ' . $user->userDetail->departemen->name
                                        : 'Belum ada Data Karyawan')
                                : 'Belum ada Data Karyawan' }}
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

            <div class="mb-3">
                <label for="job_role_id" class="form-label">Job Role</label>
                <select name="job_role_id" id="job_role_id" class="form-control select2" required>
                    <option value="">Select Job Role</option>
                    <!-- Options will be dynamically populated based on selection -->
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create User Job Role</button>
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

            $('#companyDropdown').on('change', function() {
                const companyId = $(this).val();
                resetDropdowns(['#kompartemenDropdown', '#departemenDropdown']);
                loadKompartemen(companyId);
                loadJobRoles(companyId);
            });

            $('#kompartemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $(this).val();
                resetDropdowns(['#departemenDropdown']);
                loadDepartemen(kompartemenId);
                loadJobRoles(companyId, kompartemenId);
            });

            $('#departemenDropdown').on('change', function() {
                const companyId = $('#companyDropdown').val();
                const kompartemenId = $('#kompartemenDropdown').val();
                const departemenId = $(this).val();
                loadJobRoles(companyId, kompartemenId, departemenId);
            });

            function loadKompartemen(companyId) {
                // Fetch and populate kompartemen dropdown
                $.ajax({
                    url: '/get-kompartemen',
                    method: 'GET',
                    data: {
                        company_id: companyId
                    },
                    success: function(data) {
                        populateDropdown('#kompartemenDropdown', data, 'id', 'name');
                    },
                    error: function() {
                        alert('Failed to fetch Kompartemen.');
                    }
                });
            }

            function loadDepartemen(kompartemenId) {
                // Fetch and populate departemen dropdown
                $.ajax({
                    url: '/get-departemen',
                    method: 'GET',
                    data: {
                        kompartemen_id: kompartemenId
                    },
                    success: function(data) {
                        populateDropdown('#departemenDropdown', data, 'id', 'name');
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
                        let jobRoleDropdown = $('#job_role_id');
                        jobRoleDropdown.empty().append('<option value="">Select Job Role</option>');
                        data.forEach(function(role) {
                            jobRoleDropdown.append(
                                `<option value="${role.id}">${role.job_role}</option>`);
                        });
                    },
                    error: function() {
                        alert('Failed to fetch Job Roles.');
                    }
                });
            }

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

            function resetDropdowns(selectors) {
                selectors.forEach(selector => {
                    $(selector).empty().append('<option value="">-- Select --</option>').prop('disabled',
                        true);
                });
            }
        });
    </script>
@endsection
