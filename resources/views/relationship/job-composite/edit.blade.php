@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Edit Composite Role</h1>

        <form action="{{ route('job-composite.update', $relationship->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Perusahaan</label>
                <select name="company_id" id="company_id" class="form-control" required>
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $company->id == $relationship->company_id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Job Role Dropdown -->
            <div class="mb-3">
                <label for="jabatan_id" class="form-label">Job Role</label>
                <select name="jabatan_id" id="jabatan_id" class="form-control select2" required>
                </select>
            </div>

            <!-- Composite Role Dropdown -->
            <div class="mb-3">
                <label for="composite_role_id" class="form-label">Composite Role</label>
                <select name="composite_role_id" id="composite_role_id" class="form-control select2" required>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Relationship</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize select2 for the Job Role dropdown
            $('.select2').select2({
                width: '100%',
                allowClear: true,
            });

            //Data Cache
            let jobRolesData = @json($job_roles_data);
            let compositeRolesData = @json($compositeRoles);

            // Populate Job Roles and Composite Roles with existing data
            populateJobRoles('{{ $relationship->company_id }}', '{{ $relationship->jabatan_id }}');
            populateCompositeRoles(compositeRolesData, '{{ $relationship->id }}');

            // Function to populate Job Roles
            function populateJobRoles(companyId, selectedJobRole = null) {
                $('#jabatan_id').empty().append(
                    '<option value="">Pilih Job Role</option>');

                if (jobRolesData[companyId]) {
                    $.each(jobRolesData[companyId], function(kompartemen, departemens) {
                        $.each(departemens, function(departemen, roles) {
                            let optgroupLabel = `${kompartemen} - ${departemen}`;
                            let optgroup = $('<optgroup>').attr('label', optgroupLabel);


                            $.each(roles, function(index, role) {
                                let option = $('<option>').val(role.id).text(role
                                    .nama_jabatan);
                                if (role.id == selectedJobRole) {
                                    option.attr('selected', 'selected');
                                }
                                optgroup.append(option);
                            });

                            $('#jabatan_id').append(optgroup);
                        });
                    });
                }

                $('#jabatan_id').select2({
                    allowClear: true,
                    width: '100%'
                });
            }

            // Function to populate the Composite Roles dropdown
            function populateCompositeRoles(roles, selectedCompositeRole = null) {
                $('#composite_role_id').empty().append(
                    '<option value="">Pilih Composite Role</option>');

                roles.forEach(role => {
                    let option = $('<option>').val(role.id).text(role.nama);

                    if (role.id == selectedCompositeRole) {
                        option.attr('selected',
                            'selected');
                    }
                    $('#composite_role_id').append(option);
                });

                $('#composite_role_id').select2({
                    width: '100%',
                    allowClear: true,
                });
            }

            // Handle company selection change
            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateJobRoles(companyId, {{ $relationship->jabatan_id }});
                $.get('{{ route('job-composite.company-composite') }}', {
                    company_id: companyId
                }, function(data) {
                    populateCompositeRoles(data,
                        {{ $relationship->id }}); // Populate filtered composite roles
                });
            });

            // Trigger the change event on page load to set initial Job Roles
            // $('#company_id').trigger('change');
        });
    </script>
@endsection
