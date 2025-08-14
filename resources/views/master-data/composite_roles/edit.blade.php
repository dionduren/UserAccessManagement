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
                <select name="company_id" id="company_id" class="form-control select2" required>
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->company_code }}"
                            {{ $company->company_code == $compositeRole->company_id ? 'selected' : '' }}>
                            {{ $company->nama }}
                        </option>
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
            // Initialize select2 for the Job Role dropdown
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true,
            });

            const jobRolesData = @json($job_roles_data);
            const selectedJobRoleId = {{ $compositeRole->jabatan_id ?? 'null' }};

            console.log(selectedJobRoleId);

            function populateJobRoles(companyId) {
                $('#jabatan_id').empty().append('<option value="">Select a job role</option>');

                if (jobRolesData[companyId]) {
                    $.each(jobRolesData[companyId], function(kompartemen, departemens) {
                        $.each(departemens, function(departemen, roles) {
                            let optgroupLabel = `${kompartemen} - ${departemen}`;
                            let optgroup = $('<optgroup>').attr('label', optgroupLabel);

                            $.each(roles, function(index, role) {
                                // Correctly set selected attribute
                                let selected = role.id == selectedJobRoleId ? 'selected' :
                                    '';
                                optgroup.append(
                                    $('<option>').val(role.id).text(role.nama)
                                    .attr('selected', selected)
                                );

                            });
                            $('#jabatan_id').append(optgroup);
                        });
                    });
                }

                // Reinitialize select2 with updated options
                $('#jabatan_id').select2({
                    width: '100%',
                    placeholder: 'Select a job role',
                    allowClear: true,
                });

                $("#jabatan_id").val(selectedJobRoleId).trigger('change');

            }

            // Populate Job Roles based on selected Company
            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateJobRoles(companyId);
            });

            // Trigger the change event on page load to set initial Job Roles
            $('#company_id').trigger('change');
        });
    </script>
@endsection
