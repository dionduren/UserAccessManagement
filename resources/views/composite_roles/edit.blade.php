@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Composite Role</h1>

        <form action="{{ route('composite_roles.update', $compositeRole) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control" required>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $compositeRole->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Composite Role Name</label>
                <input type="text" class="form-control" name="nama" value="{{ $compositeRole->nama }}" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi">{{ $compositeRole->deskripsi }}</textarea>
            </div>

            <div class="mb-3">
                <label for="jabatan_id" class="form-label">Job Role</label>
                <select name="jabatan_id" id="jabatan_id" class="form-control" required>
                    @foreach ($jobRoles as $jobRole)
                        <option value="{{ $jobRole->id }}"
                            {{ $compositeRole->jabatan_id == $jobRole->id ? 'selected' : '' }}>{{ $jobRole->nama_jabatan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Role</button>
        </form>
    </div>
@endsection
