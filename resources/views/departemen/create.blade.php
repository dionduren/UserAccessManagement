@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Departemen</h1>

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

        <form action="{{ route('departemens.store') }}" method="POST">
            @csrf

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control select2" required>
                </select>
            </div>

            <!-- Kompartemen Dropdown -->
            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kompartemen</label>
                <select name="kompartemen_id" id="kompartemen_id" class="form-control select2" required>
                    <option value="">Pilih Kompartemen</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Departemen Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Departemen</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            let masterData = {};

            // Fetch master data and initialize the page
            $.ajax({
                url: '/storage/master_data.json', // Replace with your actual JSON file path
                dataType: 'json',
                success: function(data) {
                    masterData = data;

                    // Populate company dropdown
                    populateDropdown('#company_id', data, 'company_id', 'company_name');
                },
                error: function() {
                    alert('Failed to load master data.');
                }
            });

            // Handle company dropdown change
            $('#company_id').on('change', function() {
                const companyId = $(this).val();

                resetDropdowns(['#kompartemen_id']);

                if (companyId) {
                    let companyData = masterData.find(c => c.company_id == companyId);

                    if (companyData) {
                        // Populate kompartemen dropdown
                        populateDropdown('#kompartemen_id', companyData.kompartemen, 'id', 'name');
                    }
                }
            });

            // Helper function to populate dropdowns
            function populateDropdown(selector, items, valueField, textField) {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">Pilih Perusahaan</option>');
                if (items?.length) {
                    dropdown.prop('disabled', false);
                    items.forEach(item => {
                        dropdown.append(`<option value="${item[valueField]}">${item[textField]}</option>`);
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
