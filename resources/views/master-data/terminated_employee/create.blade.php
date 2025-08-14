@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Add Terminated Employee</h1>

        <form method="POST" action="{{ route('terminated-employee.store') }}">
            @csrf

            <div class="mb-3">
                <label for="nik" class="form-label">NIK</label>
                <input type="text" class="form-control" id="nik" name="nik" value="{{ old('nik') }}" required>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama') }}"
                    required>
            </div>

            <div class="mb-3">
                <label for="tanggal_resign" class="form-label">Tanggal Resign</label>
                <input type="date" class="form-control" id="tanggal_resign" name="tanggal_resign"
                    value="{{ old('tanggal_resign') }}">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" class="form-control" id="status" name="status" value="{{ old('status') }}">
            </div>

            <div class="mb-3">
                <label for="last_login" class="form-label">Last Login</label>
                <input type="datetime-local" class="form-control" id="last_login" name="last_login"
                    value="{{ old('last_login') }}">
            </div>

            <div class="mb-3">
                <label for="valid_from" class="form-label">Valid From</label>
                <input type="date" class="form-control" id="valid_from" name="valid_from"
                    value="{{ old('valid_from') }}">
            </div>

            <div class="mb-3">
                <label for="valid_to" class="form-label">Valid To</label>
                <input type="date" class="form-control" id="valid_to" name="valid_to" value="{{ old('valid_to') }}">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('terminated-employee.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
