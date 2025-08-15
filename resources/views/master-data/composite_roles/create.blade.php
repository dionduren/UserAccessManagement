@extends('layouts.app')

@section('content')
    <div class="container-fluid">
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
                <h2>Create Composite Role</h2>
            </div>
            <div class="card-body">

                <form action="{{ route('composite-roles.store') }}" method="POST">
                    @csrf

                    <!-- Company Dropdown -->
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Company</label>
                        <select name="company_id" id="company_id" class="form-control" required>
                            <option value="">Select a company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Job Role Dropdown (Grouped by Kompartemen and Departemen) -->
                    <div class="mb-3">
                        <label for="jabatan_id" class="form-label">Job Role</label>
                        <select name="jabatan_id" id="jabatan_id" class="form-control select2">
                            <!-- Options will be dynamically populated based on selected Company -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Composite Role Name</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Description</label>
                        <textarea class="form-control" name="deskripsi"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Role</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize select2 on the Job Role dropdown
            $('.select2').select2({
                placeholder: 'Pilih Jabatan yang memiliki Composite Role ini',
                allowClear: true,
                width: '100%'
            });

            // Job roles data (passed from the controller)
            const jobRolesData = @json($job_roles_data);

            // Function to populate Job Roles based on selected company
            function populateJobRoles(companyId) {
                $('#jabatan_id').empty().append(
                    '<option value="">Pilih Jabatan yang memiliki Composite Role ini</option>');

                if (jobRolesData[companyId]) {
                    // Iterate over kompartemen groups
                    $.each(jobRolesData[companyId], function(kompartemen, departemens) {
                        // For each departemen in the kompartemen
                        $.each(departemens, function(departemen, roles) {
                            // Create an optgroup label based on Kompartemen and Departemen
                            let optgroupLabel = `${kompartemen} - ${departemen}`;
                            let optgroup = $('<optgroup>').attr('label', optgroupLabel);

                            // Add job roles to the optgroup
                            $.each(roles, function(index, role) {
                                optgroup.append(
                                    $('<option>').val(role.id).text(role.nama)
                                );
                            });

                            // Append the optgroup to the select
                            $('#jabatan_id').append(optgroup);
                        });
                    });
                }

                // Reinitialize select2 to update options
                $('#jabatan_id').select2({
                    placeholder: 'Pilih Jabatan yang memiliki Composite Role ini',
                    allowClear: true,
                    width: '100%'
                });
            }

            // Populate Job Roles based on selected Company
            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateJobRoles(companyId);
            });
        });
    </script>
@endsection
