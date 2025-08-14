@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Flagged Info for {{ $userDetail->nama }} ({{ $userDetail->nik }})</h2>
        <form method="POST" action="{{ route('user-detail.flagged-update', $userDetail->id) }}">
            @csrf
            <div class="mb-3">
                <label>Error Kompartemen ID</label>
                <input type="text" name="error_kompartemen_id" class="form-control"
                    value="{{ old('error_kompartemen_id', $userDetail->error_kompartemen_id) }}">
            </div>
            <div class="mb-3">
                <label>Error Kompartemen Name</label>
                <input type="text" name="error_kompartemen_name" class="form-control"
                    value="{{ old('error_kompartemen_name', $userDetail->error_kompartemen_name) }}">
            </div>
            <div class="mb-3">
                <label>Error Departemen ID</label>
                <input type="text" name="error_departemen_id" class="form-control"
                    value="{{ old('error_departemen_id', $userDetail->error_departemen_id) }}">
            </div>
            <div class="mb-3">
                <label>Error Departemen Name</label>
                <input type="text" name="error_departemen_name" class="form-control"
                    value="{{ old('error_departemen_name', $userDetail->error_departemen_name) }}">
            </div>
            <div class="mb-3">
                <label>Flagged</label>
                <select name="flagged" class="form-control">
                    <option value="1" {{ $userDetail->flagged ? 'selected' : '' }}>Flagged</option>
                    <option value="0" {{ !$userDetail->flagged ? 'selected' : '' }}>Not Flagged</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control">{{ old('keterangan', $userDetail->keterangan) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Flagged Info</button>
            <a href="{{ route('user-detail.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
