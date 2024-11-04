@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Composite Role</h1>

        <form action="{{ route('composite-roles.update', $compositeRole->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control" required>
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $company->id == $compositeRole->jobRole->company_id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Job Role Dropdown (Grouped by Kompartemen and Departemen) -->
            <div class="mb-3">
                <label for="jabatan_id" class="form-label">Job Role</label>
                <select name="jabatan_id" id="jabatan_id" class="form-control select2" required>
                    <!-- Options will be dynamically populated based on selected Company -->
                </select>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Composite Role Name</label>
                <input type="text" class="form-control" name="nama" value="{{ $compositeRole->nama }}" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi">{{ $compositeRole->deskripsi }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Role</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize select2 on the Job Role dropdown
            $('.select2').select2({
                placeholder: 'Select a job role',
                allowClear: true,
                width: '100%'
            });

            // Job roles data (passed from the controller)
            const jobRolesData = @json($job_roles_data);

            // Pre-selected job role ID for edit mode
            const selectedJobRoleId = {{ $compositeRole->jobRole->id ?? 'null' }};

            // Function to populate Job Roles based on selected company
            function populateJobRoles(companyId) {
                $('#jabatan_id').empty().append('<option value="">Select a job role</option>');

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
                                let selected = role.id == selectedJobRoleId ? 'selected' :
                                    '';
                                optgroup.append(
                                    $('<option>').val(role.id).text(role.nama_jabatan)
                                    .attr('selected', selected)
                                );
                            });

                            // Append the optgroup to the select
                            $('#jabatan_id').append(optgroup);
                        });
                    });
                }

                // Reinitialize select2 to update options
                $('#jabatan_id').select2({
                    placeholder: 'Select a job role',
                    allowClear: true,
                    width: '100%'
                });
            }

            // Populate Job Roles on initial load if company is pre-selected
            let initialCompanyId = $('#company_id').val();
            if (initialCompanyId) {
                populateJobRoles(initialCompanyId);
            }

            // Populate Job Roles based on selected Company
            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateJobRoles(companyId);
            });
        });
    </script>
@endsection
