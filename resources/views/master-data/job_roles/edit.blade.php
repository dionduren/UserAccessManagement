@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Job Role</h1>

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

        <form action="{{ route('job-roles.update', $jobRole->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Perusahaan</label>
                <select name="company_id" id="company_id" class="form-control select2" required>
                </select>
            </div>

            <!-- Kompartemen Dropdown -->
            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kompartemen</label>
                <select name="kompartemen_id" id="kompartemen_id" class="form-control select2">
                </select>
            </div>

            <!-- Departemen Dropdown -->
            <div class="mb-3">
                <label for="departemen_id" class="form-label">Departemen</label>
                <select name="departemen_id" id="departemen_id" class="form-control select2">
                </select>
            </div>

            <!-- Job Role ID -->
            <div class="mb-3">
                <label for="job_role_id" class="form-label">Job Role ID</label>
                <input type="text" class="form-control" name="job_role_id" id="job_role_id"
                    value="{{ old('job_role_id', $jobRole->job_role_id) }}" required>
            </div>

            <!-- Job Role Name -->
            <div class="mb-3">
                <label for="nama" class="form-label">Job Role</label>
                <input type="text" class="form-control" name="nama" value="{{ $jobRole->nama }}" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="deskripsi">{{ $jobRole->deskripsi }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Job Role</button>

        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            masterData = [];

            // Initialize Select2
            $('.select2').select2();

            // Fetch master data and initialize the page
            $.ajax({
                url: '/storage/master_data.json', // Replace with your actual JSON file path
                dataType: 'json',
                success: function(data) {
                    masterData = data;


                    // Populate company dropdown
                    populateDropdown('#company_id', data, 'company_id', 'company_name',
                        '{{ $jobRole->company_id }}');

                    // Load kompartemen and departemen based on the selected company
                    handleCompanyChange('{{ $jobRole->company_id }}', '{{ $jobRole->kompartemen_id }}',
                        '{{ $jobRole->departemen_id }}');

                    // Load departemen based on the selected company and kompartemen if kompartemen is not null
                    if ('{{ $jobRole->kompartemen_id }}') {
                        handleKompartemenChange('{{ $jobRole->kompartemen_id }}',
                            '{{ $jobRole->departemen_id }}');
                    }
                },
                error: function() {
                    alert('Failed to load master data.');
                }
            });

            // Handle Company dropdown change
            $('#company_id').on('change', function() {
                const companyId = $(this).val();
                handleCompanyChange(companyId);
            });

            // Handle Kompartemen dropdown change
            $('#kompartemen_id').on('change', function() {
                const kompartemenId = $(this).val();
                handleKompartemenChange(kompartemenId);
            });

            // Populate dropdowns and set selected value
            function populateDropdown(selector, items, valueField, textField, selectedValue = null) {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');
                if (items?.length) {
                    dropdown.prop('disabled', false);
                    items.forEach(item => {
                        const isSelected = item[valueField] == selectedValue ? 'selected' : '';
                        dropdown.append(
                            `<option value="${item[valueField]}" ${isSelected}>${item[textField]}</option>`
                        );
                    });
                } else {
                    dropdown.prop('disabled', true);
                }
            }

            // Reset and disable dropdowns
            function resetDropdowns(selectors) {
                selectors.forEach(selector => {
                    $(selector).empty().append('<option value="">-- Select --</option>').prop('disabled',
                        true);
                });
            }

            // Handle company dropdown change logic
            function handleCompanyChange(companyId, selectedKompartemen = null, selectedDepartemen = null) {
                resetDropdowns(['#kompartemen_id', '#departemen_id']);

                if (!companyId) return;

                let companyData = masterData.find(c => c.company_id == companyId);
                if (companyData) {
                    populateDropdown('#kompartemen_id', companyData.kompartemen, 'kompartemen_id', 'nama',
                        selectedKompartemen);

                    // Populate departemen without kompartemen
                    if (!selectedKompartemen) {
                        populateDropdown(
                            '#departemen_id',
                            companyData.departemen_without_kompartemen,
                            'departemen_id',
                            'nama',
                            selectedDepartemen
                        );
                    }
                }
            }

            // Handle kompartemen dropdown change logic
            function handleKompartemenChange(kompartemenId) {
                resetDropdowns(['#departemen_id']);

                if (!kompartemenId) return;

                const companyId = $('#company_id').val();
                const companyData = masterData.find(c => c.company_id == companyId);
                const kompartemenData = companyData?.kompartemen.find(k => k.kompartemen_id == kompartemenId);

                if (kompartemenData?.departemen?.length) {
                    populateDropdown('#departemen_id', kompartemenData.departemen, 'departemen_id', 'nama',
                        '{{ $jobRole->departemen_id }}');
                }
            }
        });
    </script>
@endsection
