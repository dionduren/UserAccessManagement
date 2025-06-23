@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Flagged Status for Job Role: {{ $jobRole->nama }}</h2>
        <form method="POST" action="{{ route('job-roles.update-flagged', $jobRole->id) }}">
            @csrf

            <div class="form-group">
                <label for="error_kompartemen_id">Error Kompartemen ID</label>
                <input type="text" class="form-control" name="error_kompartemen_id" id="error_kompartemen_id"
                    value="{{ old('error_kompartemen_id', $jobRole->error_kompartemen_id) }}">
            </div>

            <div class="form-group">
                <label for="error_kompartemen_name">Error Kompartemen Name</label>
                <input type="text" class="form-control" name="error_kompartemen_name" id="error_kompartemen_name"
                    value="{{ old('error_kompartemen_name', $jobRole->error_kompartemen_name) }}">
            </div>

            <div class="form-group">
                <label for="error_departemen_id">Error Departemen ID</label>
                <input type="text" class="form-control" name="error_departemen_id" id="error_departemen_id"
                    value="{{ old('error_departemen_id', $jobRole->error_departemen_id) }}">
            </div>

            <div class="form-group">
                <label for="error_departemen_name">Error Departemen Name</label>
                <input type="text" class="form-control" name="error_departemen_name" id="error_departemen_name"
                    value="{{ old('error_departemen_name', $jobRole->error_departemen_name) }}">
            </div>

            <div class="form-group">
                <label for="flagged-status">Flagged Status</label>
                <select class="form-control" name="flagged" id="flagged-status">
                    <option value="1" {{ $jobRole->flagged ? 'selected' : '' }}>Flagged</option>
                    <option value="0" {{ !$jobRole->flagged ? 'selected' : '' }}>Not Flagged</option>
                </select>
            </div>

            <div class="form-group">
                <label for="flagged-keterangan">Keterangan</label>
                <textarea class="form-control" name="keterangan" id="flagged-keterangan" rows="2">{{ old('keterangan', $jobRole->keterangan) }}</textarea>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update Flagged Status</button>
                <a href="{{ route('job-roles.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
@endsection
