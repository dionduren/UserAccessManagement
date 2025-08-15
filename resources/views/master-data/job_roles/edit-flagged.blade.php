@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Edit Flagged Status for Job Role: {{ $jobRole->nama }}</h2>
            </div>
            <div class="card-body">


                <form method="POST" action="{{ route('job-roles.update-flagged', $jobRole->id) }}">
                    @csrf

                    <div class="row">
                        <div class="col-1"></div>
                        <div class="col-5">
                            <div class="form-group">
                                <label for="error_kompartemen_id">Error Kompartemen ID</label>
                                <input type="text" class="form-control" name="error_kompartemen_id"
                                    id="error_kompartemen_id"
                                    value="{{ old('error_kompartemen_id', $jobRole->error_kompartemen_id) }}">
                            </div>

                            <div class="form-group">
                                <label for="error_kompartemen_name">Error Kompartemen Name</label>
                                <input type="text" class="form-control" name="error_kompartemen_name"
                                    id="error_kompartemen_name"
                                    value="{{ old('error_kompartemen_name', $jobRole->error_kompartemen_name) }}">
                            </div>
                        </div>
                        <div class="col-auto"></div>
                        <div class="col-5">
                            <div class="form-group">
                                <label for="error_departemen_id">Error Departemen ID</label>
                                <input type="text" class="form-control" name="error_departemen_id"
                                    id="error_departemen_id"
                                    value="{{ old('error_departemen_id', $jobRole->error_departemen_id) }}">
                            </div>

                            <div class="form-group">
                                <label for="error_departemen_name">Error Departemen Name</label>
                                <input type="text" class="form-control" name="error_departemen_name"
                                    id="error_departemen_name"
                                    value="{{ old('error_departemen_name', $jobRole->error_departemen_name) }}">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 mx-auto" style="width:75%;">

                    <div class="row">
                        <div class="col-1"></div>
                        <div class="col-5">
                            <div class="form-group">
                                <label for="flagged-status">Flagged Status</label>
                                <select class="form-control" name="flagged" id="flagged-status">
                                    <option value="1" {{ $jobRole->flagged ? 'selected' : '' }}>Flagged</option>
                                    <option value="0" {{ !$jobRole->flagged ? 'selected' : '' }}>Not Flagged</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-auto"></div>
                        <div class="col-5">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" name="keterangan" id="keterangan" rows="2">{{ old('keterangan', $jobRole->keterangan) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pe-5 d-flex justify-content-end gap-2">
                        <a href="{{ route('job-roles.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Update Flagged Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
