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
                <h1>Create Departemen</h1>
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

                <form action="{{ route('departemens.store') }}" method="POST">
                    @csrf

                    <!-- Company Dropdown -->
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Perusahaan</label>
                        <select name="company_id" id="company_id" class="form-control select2" required>
                            <option value="">Pilih Perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kompartemen Dropdown -->
                    <div class="mb-3">
                        <label for="kompartemen_id" class="form-label">Kompartemen</label>
                        <select name="kompartemen_id" id="kompartemen_id" class="form-control select2">
                            <option value="">Pilih Kompartemen</option>
                        </select>
                    </div>

                    <!-- Departemen ID -->
                    <div class="mb-3">
                        <label for="departemen_id" class="form-label">Departemen ID</label>
                        <input type="text" class="form-control" name="departemen_id" required>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Departemen</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>

                    <div class="mb-3">
                        <label for="cost_center" class="form-label">Cost Center</label>
                        <input type="text" class="form-control" name="cost_center">
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi"></textarea>
                    </div>


                    <button type="submit" class="btn btn-primary">Buat Departemen</button>
                </form>
            </div>
        </div>
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
                    // populateDropdown('#company_id', data, 'company_id', 'company_name');
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
                        populateDropdown('#kompartemen_id', companyData.kompartemen, 'kompartemen_id',
                            'nama');
                    }
                }
            });

            // Helper function to populate dropdowns
            function populateDropdown(selector, items, valueField, textField) {
                let dropdown = $(selector);
                dropdown.empty().append('<option value="">Pilih Perusahaan</option>');
                if (items?.length) {
                    items.sort((a, b) => a[textField].localeCompare(b[textField]));
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
