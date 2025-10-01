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
                <h1>Edit Composite Role</h1>
            </div>
            <div class="card-body">

                <form action="{{ route('job-composite.update', $relationship->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Company Dropdown -->
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Perusahaan</label>
                        <select name="company_id" id="company_id" class="form-control" required>
                            <option value="">Pilih Perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}"
                                    {{ $company->company_code == $relationship->company_id ? 'selected' : '' }}>
                                    {{ $company->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Job Role Dropdown -->
                    <div class="mb-3">
                        <label for="jabatan_id" class="form-label">Job Role</label>
                        <select name="jabatan_id" id="jabatan_id" class="form-control select2" required></select>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="toggleAllJobRoles">
                            <label class="form-check-label" for="toggleAllJobRoles">
                                Tampilkan semua Job Role di perusahaan ini
                            </label>
                        </div>
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
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#jabatan_id, #composite_role_id').select2({
                width: '100%',
                allowClear: true,
            });

            let jobRolesDataUnassigned = @json($job_roles_data);
            let jobRolesDataAll = @json($all_job_roles_data);
            let compositeRolesData = @json($compositeRoles);
            let useAllJobRoles = false;

            populateJobRoles('{{ $relationship->company_id }}', '{{ $relationship->jabatan_id }}');
            populateCompositeRoles(compositeRolesData, '{{ $relationship->id }}');

            function populateJobRoles(companyId, selectedJobRole = null) {
                const dataset = useAllJobRoles ? jobRolesDataAll : jobRolesDataUnassigned;
                let companyData = dataset[companyId] || null;

                if (!companyData && !useAllJobRoles) {
                    companyData = jobRolesDataAll[companyId] || null;
                }

                let $select = $('#jabatan_id');
                $select.empty().append('<option value="">Pilih Job Role</option>');

                if (companyData) {
                    $.each(companyData, function(kompartemen, departemens) {
                        $.each(departemens, function(departemen, roles) {
                            let optgroupLabel = `${kompartemen} - ${departemen}`;
                            let optgroup = $('<optgroup>').attr('label', optgroupLabel);

                            roles.forEach(role => {
                                let option = $('<option>').val(role.id).text(role.nama);
                                if (String(role.id) === String(selectedJobRole)) {
                                    option.attr('selected', 'selected');
                                }
                                optgroup.append(option);
                            });

                            $select.append(optgroup);
                        });
                    });
                }

                $select.trigger('change.select2');

                if (!$select.val() && selectedJobRole) {
                    $select.val(String(selectedJobRole)).trigger('change.select2');
                }
            }

            function populateCompositeRoles(roles, selectedCompositeRole = null) {
                const $select = $('#composite_role_id');
                $select.empty().append('<option value="">Pilih Composite Role</option>');

                roles.forEach(role => {
                    let option = $('<option>').val(role.id).text(role.nama);
                    if (String(role.id) === String(selectedCompositeRole)) {
                        option.attr('selected', 'selected');
                    }
                    $select.append(option);
                });

                $select.trigger('change.select2');
            }

            $('#toggleAllJobRoles').on('change', function() {
                useAllJobRoles = this.checked;
                populateJobRoles($('#company_id').val(), '{{ $relationship->jabatan_id }}');
            });

            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateJobRoles(companyId, '{{ $relationship->jabatan_id }}');

                $.get('{{ route('job-composite.company-composite') }}', {
                    company_id: companyId
                }, function(data) {
                    populateCompositeRoles(data, '{{ $relationship->id }}');
                });
            });

            if (!$('#jabatan_id').val()) {
                $('#toggleAllJobRoles').prop('checked', true);
                useAllJobRoles = true;
                populateJobRoles('{{ $relationship->company_id }}', '{{ $relationship->jabatan_id }}');
            }
        });
    </script>
@endsection
