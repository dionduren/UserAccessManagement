@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Tcode</h1>

        <form action="{{ route('tcodes.store') }}" method="POST">
            @csrf

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Nama Perusahaan</label>
                <select name="company_id" id="company_id" class="form-control">
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Single Role Dropdown with Search -->
            <div class="mb-3">
                <label for="single_role_id" class="form-label">Assign Single Role</label>
                <select name="single_role_id" id="single_role_id" class="form-control select2">
                    <option value="">Pilih role</option>
                    @foreach ($single_roles as $singleRole)
                        <option value="{{ $singleRole->id }}">{{ $singleRole->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="code" class="form-label">Nama Tcode</label>
                <input type="text" class="form-control" name="code" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="deskripsi"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Tcode</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2(); // Initialize Select2 for searchable dropdown
        });
    </script>
@endsection
