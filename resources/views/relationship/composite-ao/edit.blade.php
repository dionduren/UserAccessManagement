@extends('layouts.app')

@section('header-scripts')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Edit Composite Authorization Object</h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('composite_ao.update', $compositeAO->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select name="company_id" id="company_id" class="form-select" required>
                                <option value="">-- Select Company --</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->company_code }}" @selected(old('company_id', $compositeAO->compositeRole?->company_id) === $company->company_code)>
                                        {{ $company->nama }} ({{ $company->company_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="composite_role_id" class="form-label">Composite Role <span
                                    class="text-danger">*</span></label>
                            <select name="composite_role_id" id="composite_role_id" class="form-select" required>
                                <option value="">-- Loading... --</option>
                            </select>
                            <small class="text-muted">Value saat ini:
                                {{ old('composite_role_id', $compositeAO->compositeRole?->nama) }}</small>
                            <small class="text-muted d-block">Tidak menemukan Composite Role yang anda cari? cek di laman <a
                                    href="{{ route('composite-roles.index') }}" class="btn btn-sm btn-primary"
                                    target="_blank">Composite
                                    Roles </a></small>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ao_name" class="form-label">Authorization Object Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="ao_name" id="ao_name" class="form-control"
                                value="{{ old('ao_name', $compositeAO->nama) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="ao_description" class="form-label">Description</label>
                            <input type="text" name="ao_description" id="ao_description" class="form-control"
                                value="{{ old('ao_description', $compositeAO->deskripsi) }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('composite_ao.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const companySelect = document.getElementById('company_id');
            const compositeRoleSelect = document.getElementById('composite_role_id');
            const currentCompositeRoleId = {{ $compositeAO->compositeRole?->id ?? 'null' }};

            // Load composite roles on page load
            loadCompositeRoles(companySelect.value, currentCompositeRoleId);

            // Load composite roles when company changes
            companySelect.addEventListener('change', function() {
                loadCompositeRoles(this.value);
            });

            function loadCompositeRoles(companyId, selectedId = null) {
                // Destroy Select2 if already initialized
                if ($(compositeRoleSelect).hasClass("select2-hidden-accessible")) {
                    $(compositeRoleSelect).select2('destroy');
                }

                compositeRoleSelect.innerHTML = '<option value="">-- Loading... --</option>';
                compositeRoleSelect.disabled = true;

                if (!companyId) {
                    compositeRoleSelect.innerHTML = '<option value="">-- Select Company First --</option>';
                    return;
                }

                fetch(`{{ route('composite-single.filter-company') }}?company_id=${companyId}`)
                    .then(r => r.json())
                    .then(data => {
                        compositeRoleSelect.innerHTML = '<option value="">-- Select Composite Role --</option>';
                        data.compositeRoles.forEach(cr => {
                            const opt = document.createElement('option');
                            opt.value = cr.id;
                            opt.textContent = cr.nama;
                            if (selectedId && cr.id === selectedId) {
                                opt.selected = true;
                            }
                            compositeRoleSelect.appendChild(opt);
                        });
                        compositeRoleSelect.disabled = false;

                        // Initialize Select2 after loading options
                        $(compositeRoleSelect).select2({
                            theme: 'bootstrap-5',
                            placeholder: '-- Search Composite Role --',
                            allowClear: true,
                            width: '100%'
                        });
                    })
                    .catch(() => {
                        compositeRoleSelect.innerHTML = '<option value="">-- Error Loading --</option>';
                    });
            }
        });
    </script>
@endsection
