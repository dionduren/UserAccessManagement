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
                <h1>Buat Relationship antara Job Role Composite Role</h1>
            </div>
            <div class="card-body">

                <!-- Error Display -->
                @if (session('validationErrors') || session('error'))
                    <div class="alert alert-danger">
                        <h4>Error(s) occurred:</h4>
                        <ul>
                            <!-- Validation Errors -->
                            @if (session('validationErrors'))
                                @foreach (session('validationErrors') as $row => $messages)
                                    <li>Row {{ $row }}:
                                        <ul>
                                            @foreach ($messages as $message)
                                                <li>{{ $message }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            @endif

                            <!-- General Error -->
                            @if (session('error'))
                                <li>{{ session('error') }}</li>
                            @endif
                        </ul>
                    </div>
                @endif

                <!-- Laravel Validation Errors -->
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


                <!-- Success Message -->
                @if (session('success'))
                    <div class="alert alert-success">
                        <h4>Success:</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('job-composite.store') }}" method="POST">
                    @csrf

                    <!-- Company Dropdown -->
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Perusahaan</label>
                        <select name="company_id" id="company_id" class="form-control" required>
                            <option value="">Pilih Perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Job Role Dropdown (Grouped by Kompartemen and Departemen) -->
                    <div class="mb-3">
                        <label for="jabatan_id" class="form-label">Job Role</label>
                        <select name="jabatan_id" id="jabatan_id" class="form-control select2" required>
                            <option value="">Pilih Job Role yang belum memiliki Composite Role</option>
                            <!-- Options will be dynamically populated based on selected Company -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="composite_role_id" class="form-label">Composite Role</label>
                        <select name="composite_role_id" id="composite_role_id" class="form-control select2" required>
                            <option value="">Pilih Composite Role yang belum memiliki Job Role</option>
                            <!-- Options will be dynamically populated based on selected Company -->
                            @foreach ($compositeRoles as $composite)
                                <option value="{{ $composite->id }}">{{ $composite->company?->shortname ?? '' }} -
                                    {{ $composite->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Relationship</button>
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
                allowClear: true,
                width: '100%'
            });



            // Job Roles Data Cache
            let jobRolesData = @json($job_roles_data);
            populateAllJobRoles();

            // Function to populate Job Roles based on selected company
            function populateJobRoles(companyId) {
                $('#jabatan_id').empty().append(
                    '<option value="">Pilih Job Role yang belum memiliki Composite Role</option>');

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
                    // placeholder: 'Pilih Job Role yang belum memiliki Composite Role',
                    width: '100%',
                    allowClear: true,
                });
            }

            // Function to populate Composite Roles based on Company
            function populateCompositeRoles(companyId) {
                $('#composite_role_id').empty().append(
                    '<option value="">Pilih Composite Role yang belum memiliki Job Role</option>');

                $.get('{{ route('job-composite.empty-composite') }}', {
                    company_id: companyId
                }, function(data) {
                    $.each(data, function(index, compositeRole) {
                        $('#composite_role_id').append(
                            $('<option>').val(compositeRole.id).text(compositeRole.nama)
                        );
                    });
                });

                $('#composite_role_id').select2({
                    width: '100%',
                    allowClear: true,
                });
            }

            // Function to populate all Job Roles
            function populateAllJobRoles() {
                $('#jabatan_id').empty().append(
                    '<option value="">Pilih Job Role yang belum memiliki Composite Role</option>');

                $.each(jobRolesData, function(company, kompartemenGroups) {
                    $.each(kompartemenGroups, function(kompartemen, departemens) {
                        $.each(departemens, function(departemen, roles) {
                            let optgroupLabel =
                                // `${company} - ${kompartemen} - ${departemen}`;
                                `${kompartemen} - ${departemen}`;
                            let optgroup = $('<optgroup>').attr('label', optgroupLabel);

                            $.each(roles, function(index, role) {
                                $text = role.company_shortname + ' - ' + role
                                    .nama;
                                optgroup.append($('<option>').val(role.id).text(
                                    $text));
                            });

                            $('#jabatan_id').append(optgroup);
                        });
                    });
                });

                $('#jabatan_id').select2({
                    width: '100%',
                    allowClear: true,
                });
            }

            // Populate Job Roles based on selected Company
            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateJobRoles(companyId);
                populateCompositeRoles(companyId);
            });
        });
    </script>
@endsection
