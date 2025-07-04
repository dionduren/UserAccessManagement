@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">
            Edit Flagged Status for User Generic:
            <span class="text-primary">{{ $userGeneric->user_code }}</span>
        </h2>
        <form method="POST" action="{{ route('user-generic.flagged-update', $userGeneric->id) }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="periode_id" class="form-label">Periode</label>
                    <select class="form-select" name="periode_id" id="periode_id">
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}"
                                {{ old('periode_id', $userGeneric->periode_id) == $periode->id ? 'selected' : '' }}>
                                {{ $periode->description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="error_kompartemen_id" class="form-label">Error Kompartemen ID</label>
                    <input type="text" class="form-control" name="error_kompartemen_id" id="error_kompartemen_id"
                        value="{{ old('error_kompartemen_id', $userGeneric->error_kompartemen_id) }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="kompartemen_name" class="form-label">Kompartemen Name</label>
                    <input type="text" class="form-control" name="kompartemen_name" id="kompartemen_name"
                        value="{{ old('kompartemen_name', $userGeneric->kompartemen_name) }}">
                </div>
                <div class="col-md-6">
                    <label for="error_departemen_id" class="form-label">Error Departemen ID</label>
                    <input type="text" class="form-control" name="error_departemen_id" id="error_departemen_id"
                        value="{{ old('error_departemen_id', $userGeneric->error_departemen_id) }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="departemen_name" class="form-label">Departemen Name</label>
                    <input type="text" class="form-control" name="departemen_name" id="departemen_name"
                        value="{{ old('departemen_name', $userGeneric->departemen_name) }}">
                </div>
                <div class="col-md-6">
                    <label for="error_job_role_id" class="form-label">Error Job Role ID</label>
                    <input type="text" class="form-control" name="error_job_role_id" id="error_job_role_id"
                        value="{{ old('error_job_role_id', $userGeneric->error_job_role_id) }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="job_role_name" class="form-label">Job Role Name</label>
                    <input type="text" class="form-control" name="job_role_name" id="job_role_name"
                        value="{{ old('job_role_name', $userGeneric->job_role_name) }}">
                </div>
                <div class="col-md-6">
                    <label for="flagged-status" class="form-label">Flagged Status</label>
                    <select class="form-select" name="flagged" id="flagged-status">
                        <option value="1" {{ $userGeneric->flagged ? 'selected' : '' }}>Flagged</option>
                        <option value="0" {{ !$userGeneric->flagged ? 'selected' : '' }}>Not Flagged</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="keterangan_flagged" class="form-label">Keterangan Flagged</label>
                <textarea class="form-control" name="keterangan_flagged" id="keterangan_flagged" rows="3">{{ old('keterangan_flagged', $userGeneric->keterangan_flagged) }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update Flagged Status</button>
                <a href="{{ route('user-generic.index') }}" class="btn btn-secondary ms-2">Back</a>
            </div>
        </form>
    </div>
@endsection
