@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Buat Master Data Job Role Baru</h1>

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

        <form id="jobRoleForm" action="{{ route('job-roles.store') }}" method="POST">
            @csrf

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Perusahaan</label>
                <select name="company_id" id="company_id" class="form-control select2" required>
                    <option value="">Pilih Perusahaan</option>
                </select>
            </div>

            <!-- Kompartemen Dropdown -->
            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kompartemen</label>
                <select name="kompartemen_id" id="kompartemen_id" class="form-control select2">
                    <option value="">Pilih Kompartemen</option>
                </select>
            </div>

            <!-- Departemen Dropdown -->
            <div class="mb-3">
                <label for="departemen_id" class="form-label">Departemen</label>
                <select name="departemen_id" id="departemen_id" class="form-control select2">
                    <option value="">Pilih Departemen</option>
                </select>
            </div>

            <!-- Job Role ID -->
            <div class="mb-3">
                <label for="job_role_id" class="form-label">
                    Job Role ID
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="generateJobRoleIdBtn"
                        title="Generate Latest Job Role ID">
                        Generate
                    </button>
                </label>
                <input type="text" class="form-control" name="job_role_id" id="job_role_id"
                    value="{{ old('job_role_id') }}" readonly>
            </div>

            <!-- Job Role Name -->
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Job Role</label>
                <input type="text" class="form-control" name="nama" value="{{ old('nama') }}" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="deskripsi">{{ old('deskripsi') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Buat Job Role</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            let masterData = {};
            const oldCompanyId = '{{ old('company_id') }}';
            const oldKompartemenId = '{{ old('kompartemen_id') }}';
            const oldDepartemenId = '{{ old('departemen_id') }}';

            // Fetch master data and initialize the page
            $.ajax({
                url: '/storage/master_data.json', // Replace with your actual JSON file path
                dataType: 'json',
                success: function(data) {
                    masterData = data;

                    // Populate company dropdown
                    populateDropdown('#company_id', data, 'company_id', 'company_name', oldCompanyId);

                    if (oldCompanyId) {
                        const selectedCompany = masterData.find(c => c.company_id == oldCompanyId);
                        if (selectedCompany) {
                            populateDropdown('#kompartemen_id', selectedCompany.kompartemen, 'id',
                                'nama', oldKompartemenId);
                            populateDropdown('#departemen_id', selectedCompany
                                .departemen_without_kompartemen, 'id', 'nama', oldDepartemenId);
                        }
                    }
                },
                error: function() {
                    alert('Failed to load master data.');
                }
            });

            // Handle company dropdown change
            $('#company_id').on('change', function() {
                const companyId = $(this).val();

                resetDropdowns(['#kompartemen_id', '#departemen_id']);

                if (companyId) {
                    let companyData = masterData.find(c => c.company_id == companyId);
                    if (companyData) {
                        populateDropdown('#kompartemen_id', companyData.kompartemen, 'id', 'nama');
                        populateDropdown('#departemen_id', companyData.departemen_without_kompartemen, 'id',
                            'nama');
                    }
                }
            });

            // Handle kompartemen dropdown change
            $('#kompartemen_id').on('change', function() {
                const companyId = $('#company_id').val();
                const kompartemenId = $(this).val();

                resetDropdowns(['#departemen_id']);

                if (companyId && kompartemenId) {
                    let companyData = masterData.find(c => c.company_id == companyId);
                    let kompartemenData = companyData?.kompartemen.find(k => k.id == kompartemenId);

                    if (kompartemenData?.departemen.length) {
                        populateDropdown('#departemen_id', kompartemenData.departemen, 'id', 'nama');
                    }
                }
            });

            // Handle Job Role ID generation
            $('#generateJobRoleIdBtn').on('click', function() {
                const companyId = $('#company_id').val();
                const kompartemenId = $('#kompartemen_id').val();
                const departemenId = $('#departemen_id').val();

                if (!companyId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Pilih perusahaan terlebih dahulu.'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('job-roles.generate-job-role-id') }}',
                    data: {
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    success: function(res) {
                        if (res.job_role_id) {
                            $('#job_role_id').val(res.job_role_id);
                        } else if (res.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.error,
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.error ||
                                'Gagal generate Job Role ID.',
                        });
                    }
                });
            });

            // Helper function to populate dropdowns
            function populateDropdown(selector, items, valueField, textField, selectedValue = '') {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');
                if (items?.length) {
                    dropdown.prop('disabled', false);
                    items.forEach(item => {
                        dropdown.append(
                            `<option value="${item[valueField]}" ${item[valueField] == selectedValue ? 'selected' : ''}>${item[textField]}</option>`
                        );
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
        });
    </script>
@endsection
