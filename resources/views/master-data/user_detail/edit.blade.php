@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit User Detail</h5>
                <a href="{{ route('user-detail.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form id="editForm" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nik" class="form-label">NIK *</label>
                            <input type="text" class="form-control" id="nik" name="nik"
                                value="{{ $userDetail->nik }}" required>
                            <div class="invalid-feedback">NIK is required</div>
                        </div>

                        <div class="col-md-6">
                            <label for="nama" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                value="{{ $userDetail->nama }}" required>
                            <div class="invalid-feedback">Name is required</div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ $userDetail->email }}" required>
                            <div class="invalid-feedback">Valid email is required</div>
                        </div>

                        <div class="col-md-6">
                            <label for="company_id" class="form-label">Company *</label>
                            <select class="form-select" id="company_id" name="company_id" required>
                                <option value="">Choose...</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->company_code }}"
                                        {{ $userDetail->company_id == $company->company_code ? 'selected' : '' }}>
                                        {{ $company->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Company is required</div>
                        </div>

                        <div class="col-md-6">
                            <label for="direktorat" class="form-label">Direktorat</label>
                            <input type="text" class="form-control" id="direktorat" name="direktorat"
                                value="{{ $userDetail->direktorat }}">
                        </div>

                        <div class="col-md-6">
                            <label for="kompartemen_id" class="form-label">Kompartemen</label>
                            <select class="form-select" id="kompartemen_id" name="kompartemen_id">
                                <option value="">Choose...</option>
                                @foreach ($kompartemen as $k)
                                    <option value="{{ $k->kompartemen_id }}"
                                        {{ $userDetail->kompartemen_id == $k->kompartemen_id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="departemen_id" class="form-label">Departemen</label>
                            <select class="form-select" id="departemen_id" name="departemen_id">
                                <option value="">Choose...</option>
                                @foreach ($departemen as $d)
                                    <option value="{{ $d->departemen_id }}"
                                        {{ $userDetail->departemen_id == $d->departemen_id ? 'selected' : '' }}>
                                        {{ $d->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editForm');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(form);

                fetch('{{ route('user-detail.update', $userDetail->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = '{{ route('user-detail.index') }}';
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating'
                        });
                    });
            });
        });
    </script>
@endsection
