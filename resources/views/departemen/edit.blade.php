@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Departemen</h1>

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
                </select>
            </div>

            <!-- Kompartemen Dropdown -->
            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kompartemen</label>
                <select name="kompartemen_id" id="kompartemen_id" class="form-control select2" required>
                </select>
            </div>

            <!-- Departemen Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Departemen Name</label>
                <input type="text" class="form-control" name="name" value="{{ $departemen->name }}" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description">{{ $departemen->description }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Departemen</button>
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
                        '{{ $company->id }}');

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
                    populateDropdown('#kompartemen_id', companyData.kompartemen, 'id', 'name', selectedKompartemen);
                }
            }

        });
    </script>
@endsection
