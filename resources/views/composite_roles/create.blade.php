@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Composite Role</h1>

        <form action="{{ route('composite_roles.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control" required>
                    <!-- Populate options dynamically based on companies available -->
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Composite Role Name</label>
                <input type="text" class="form-control" name="nama" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi"></textarea>
            </div>

            <div class="mb-3">
                <label for="jabatan_id" class="form-label">Job Role</label>
                <select name="jabatan_id" id="jabatan_id" class="form-control" required>
                    <!-- Populate options dynamically based on job roles available -->
                    @foreach ($jobRoles as $jobRole)
                        <option value="{{ $jobRole->id }}">{{ $jobRole->nama_jabatan }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Role</button>
        </form>
    </div>
@endsection
