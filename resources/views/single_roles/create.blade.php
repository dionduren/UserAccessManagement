@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Single Role</h1>

        <form action="{{ route('single-roles.store') }}" method="POST">
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

            <!-- Composite Role Dropdown -->
            <div class="mb-3">
                <label for="composite_role_id" class="form-label">Composite Role</label>
                <select name="composite_role_id" id="composite_role_id" class="form-control select2">
                    <option value="">Select a Composite Role</option>
                    <!-- Options populated dynamically based on selected Company -->
                </select>
            </div>

            <!-- Name Field -->
            <div class="mb-3">
                <label for="nama" class="form-label">Single Role Name</label>
                <input type="text" class="form-control" name="nama" id="nama"
                    placeholder="Enter the single role name" required>
            </div>

            <!-- Description Field -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" placeholder="Enter a description"></textarea>
            </div>

            <!-- Tcodes Multi-select Dropdown (For Future To-Do List) -->
            {{-- <div class="mb-3">
                <label for="tcodes" class="form-label">Assign Tcodes</label>
                <select name="tcodes[]" id="tcodes" class="form-control select2" multiple="multiple">
                    @foreach ($tcodes as $tcode)
                        <option value="{{ $tcode->id }}">{{ $tcode->code }} - {{ $tcode->description }}</option>
                    @endforeach
                </select>
            </div> --}}

            <button type="submit" class="btn btn-primary">Create Single Role</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            // Load Composite Roles based on selected Company
            $('#company_id').on('change', function() {
                const companyId = $(this).val();
                $('#composite_role_id').empty().append('<option value="">Select a Composite Role</option>');

                if (companyId) {
                    $.get('{{ route('single-roles.filtered-data') }}', {
                        company_id: companyId
                    }, function(data) {
                        data.compositeRoles.forEach(role => {
                            $('#composite_role_id').append(
                                `<option value="${role.id}">${role.nama}</option>`
                            );
                        });
                    });
                }
            });
        });
    </script>
@endsection
