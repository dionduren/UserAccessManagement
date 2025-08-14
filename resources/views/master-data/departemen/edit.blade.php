@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- General Error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h1>Edit Departemen</h1>
            </div>
            <div class="card-body">

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

                <form action="{{ route('departemens.update', $departemen) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Company Dropdown -->
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Perusahaan</label>
                        <select name="company_id" id="company_id" class="form-control select2" required>
                            <option value="">Pilih Perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}"
                                    {{ $company->company_code == $departemen->company_id ? 'selected' : '' }}>
                                    {{ $company->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kompartemen Dropdown -->
                    <div class="mb-3">
                        <label for="kompartemen_id" class="form-label">Kompartemen</label>
                        <select name="kompartemen_id" id="kompartemen_id" class="form-control select2" required>
                        </select>
                    </div>

                    <!-- Departemen ID -->
                    <div class="mb-3">
                        <label for="departemen_id" class="form-label">Departemen ID</label>
                        <input type="text" class="form-control" name="departemen_id"
                            value="{{ $departemen->departemen_id }}" required>
                    </div>

                    <!-- Departemen Name -->
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Departemen</label>
                        <input type="text" class="form-control" name="nama" value="{{ $departemen->nama }}" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi">{{ $departemen->deskripsi }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Departemen</button>
                </form>
            </div>
        </div>
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
                    // populateDropdown('#company_id', data, 'company_id', 'company_name',
                    //     '{{ $company->company_code }}');

                    // Load kompartemen based on the selected company
                    handleCompanyChange('{{ $departemen->company_id }}',
                        '{{ $departemen->kompartemen_id }}');
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

            // Populate dropdowns and set selected value
            function populateDropdown(selector, items, valueField, textField, selectedValue = null) {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">-- Select --</option>');
                if (items?.length) {
                    dropdown.prop('disabled', false);
                    items.sort((a, b) => a[valueField].localeCompare(b[valueField]));
                    items.forEach(item => {
                        const isSelected = item[valueField] == selectedValue ? 'selected' : '';
                        console.log('valueField = ', valueField, ' - ', item[valueField], ' == ',
                            selectedValue, ' ? ', isSelected);
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
            function handleCompanyChange(companyId, selectedKompartemen = null) {
                resetDropdowns(['#kompartemen_id']);

                if (!companyId) return;

                let companyData = masterData.find(c => c.company_id == companyId);
                if (companyData) {
                    populateDropdown('#kompartemen_id', companyData.kompartemen, 'kompartemen_id', 'nama',
                        selectedKompartemen);
                }
            }

        });
    </script>
@endsection
