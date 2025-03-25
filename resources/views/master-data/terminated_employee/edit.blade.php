@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Terminated Employee</h1>

        <form method="POST" action="{{ route('terminated-employee.update', $terminated_employee) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nik" class="form-label">NIK</label>
                <input type="text" class="form-control" id="nik" name="nik"
                    value="{{ old('nik', $terminated_employee->nik) }}" required>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama"
                    value="{{ old('nama', $terminated_employee->nama) }}" required>
            </div>

            <div class="mb-3">
                <label for="tanggal_resign" class="form-label">Tanggal Resign</label>
                <input type="date" class="form-control" id="tanggal_resign" name="tanggal_resign"
                    value="{{ old('tanggal_resign', $terminated_employee->tanggal_resign ? date('Y-m-d', strtotime($terminated_employee->tanggal_resign)) : '') }}">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" class="form-control" id="status" name="status"
                    value="{{ old('status', $terminated_employee->status) }}">
            </div>

            <div class="mb-3">
                <label for="last_login" class="form-label">Last Login</label>
                <input type="datetime-local" class="form-control" id="last_login" name="last_login"
                    value="{{ old('last_login', $terminated_employee->last_login ? date('Y-m-d\TH:i', strtotime($terminated_employee->last_login)) : '') }}">
            </div>

            <div class="mb-3">
                <label for="valid_from" class="form-label">Valid From</label>
                <input type="date" class="form-control @error('valid_from') is-invalid @enderror" id="valid_from"
                    name="valid_from"
                    value="{{ old('valid_from', is_null($terminated_employee->valid_from) ? '' : date('Y-m-d', strtotime($terminated_employee->valid_from))) }}">
                @error('valid_from')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="valid_to" class="form-label">Valid To</label>
                <input type="date" class="form-control @error('valid_to') is-invalid @enderror" id="valid_to"
                    name="valid_to"
                    value="{{ old('valid_to', is_null($terminated_employee->valid_to) ? '' : date('Y-m-d', strtotime($terminated_employee->valid_to))) }}">
                @error('valid_to')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('terminated-employee.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
