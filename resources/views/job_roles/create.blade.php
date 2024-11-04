@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Job Role</h1>

        <form action="{{ route('job-roles.store') }}" method="POST">
            @csrf

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control" required>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="nama_jabatan" class="form-label">Job Role Name</label>
                <input type="text" class="form-control" name="nama_jabatan" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Job Role</button>
        </form>
    </div>
@endsection
