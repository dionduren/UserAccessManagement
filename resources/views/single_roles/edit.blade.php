@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Single Role</h1>

        <form action="{{ route('single-roles.update', $singleRole->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control select2" required>
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ $company->id == $singleRole->company_id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Composite Role Dropdown -->
            <div class="mb-3">
                <label for="composite_role_id" class="form-label">Composite Role</label>
                <select name="composite_role_id" id="composite_role_id" class="form-control select2">
                    <option value="">Select a Composite Role</option>
                    @foreach ($compositeRoles as $role)
                        <option value="{{ $role->id }}"
                            {{ $role->id == $singleRole->composite_role_id ? 'selected' : '' }}>
                            {{ $role->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Name Field -->
            <div class="mb-3">
                <label for="nama" class="form-label">Single Role Name</label>
                <input type="text" class="form-control" name="nama" id="nama" value="{{ $singleRole->nama }}"
                    required>
            </div>

            <!-- Description Field -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi">{{ $singleRole->deskripsi }}</textarea>
            </div>

            <!-- Tcodes Multi-select (For Future Use) -->
            <div class="mb-3">
                <label for="tcodes" class="form-label">Assign Tcodes</label>
                <select name="tcodes[]" id="tcodes" class="form-control select2" multiple="multiple">
                    @foreach ($tcodes as $tcode)
                        <option value="{{ $tcode->id }}"
                            {{ in_array($tcode->id, $singleRole->tcodes->pluck('id')->toArray()) ? 'selected' : '' }}>
                            {{ $tcode->code }} - {{ $tcode->description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Single Role</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            // Populate Composite Role dropdown based on selected Company
            $('#company_id').on('change', function() {
                let companyId = $(this).val();
                $('#composite_role_id').empty().append('<option value="">Select a Composite Role</option>');

                if (companyId) {
                    $.get('{{ route('single-roles.filtered-data') }}', {
                        company_id: companyId
                    }, function(data) {
                        data.compositeRoles.forEach(role => {
                            let selected = role.id ===
                                {{ $singleRole->composite_role_id ?? 'null' }} ?
                                'selected' : '';
                            $('#composite_role_id').append(
                                `<option value="${role.id}" ${selected}>${role.nama}</option>`
                            );
                        });
                    });
                }
            });

            // Trigger initial population for Composite Role dropdown
            $('#company_id').trigger('change');
        });
    </script>
@endsection
