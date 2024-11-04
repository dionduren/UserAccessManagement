@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Job Role</h1>
        <form action="{{ route('job-roles.store') }}" method="POST">
            @csrf

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control select2" required>
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Kompartemen Dropdown -->
            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kompartemen</label>
                <select name="kompartemen_id" id="kompartemen_id" class="form-control select2" required>
                    <option value="">Select a Kompartemen</option>
                </select>
            </div>

            <!-- Departemen Dropdown -->
            <div class="mb-3">
                <label for="departemen_id" class="form-label">Departemen</label>
                <select name="departemen_id" id="departemen_id" class="form-control select2" required>
                    <option value="">Select a Departemen</option>
                </select>
            </div>

            <!-- Job Role Name -->
            <div class="mb-3">
                <label for="nama_jabatan" class="form-label">Job Role Name</label>
                <input type="text" class="form-control" name="nama_jabatan" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Job Role</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all dropdowns
            $('.select2').select2();

            // Load Kompartemen based on selected Company
            $('#company_id').on('change', function() {
                const companyId = $(this).val();
                $('#kompartemen_id').empty().append('<option value="">Select a Kompartemen</option>');
                $('#departemen_id').empty().append('<option value="">Select a Departemen</option>');

                if (companyId) {
                    $.get('{{ route('job-roles.filtered-data') }}', {
                        company_id: companyId
                    }, function(data) {
                        data.kompartemens.forEach(kompartemen => {
                            $('#kompartemen_id').append(
                                `<option value="${kompartemen.id}">${kompartemen.name}</option>`
                            );
                        });
                    });
                }
            });

            // Load Departemen based on selected Kompartemen
            $('#kompartemen_id').on('change', function() {
                const kompartemenId = $(this).val();
                $('#departemen_id').empty().append('<option value="">Select a Departemen</option>');

                if (kompartemenId) {
                    $.get('{{ route('job-roles.filtered-data') }}', {
                        kompartemen_id: kompartemenId
                    }, function(data) {
                        data.departemens.forEach(departemen => {
                            $('#departemen_id').append(
                                `<option value="${departemen.id}">${departemen.name}</option>`
                            );
                        });
                    });
                }
            });
        });
    </script>
@endsection
