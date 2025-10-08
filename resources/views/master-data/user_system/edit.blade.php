@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit User System</h2>
        <form action="{{ route('user-system.update', $record->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="periode_id" class="form-label">Periode</label>
                <select class="form-control" id="periode_id" name="periode_id" required>
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}"
                            {{ old('periode_id', $record->periode_id) == $periode->id ? 'selected' : '' }}>
                            {{ $periode->definisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="group" class="form-label">Perusahaan (Shortname)</label>
                <input type="text" class="form-control" id="group" name="group"
                    value="{{ old('group', $record->group) }}">
            </div>

            <div class="mb-3">
                <label for="user_code" class="form-label">User Code</label>
                <input type="text" class="form-control" id="user_code" name="user_code"
                    value="{{ old('user_code', $record->user_code) }}" required>
            </div>

            <div class="mb-3">
                <label for="user_profile" class="form-label">Nama User</label>
                <input type="text" class="form-control" id="user_profile" name="user_profile"
                    value="{{ old('user_profile', $record->user_profile) }}">
            </div>

            <div class="mb-3">
                <label for="user_type" class="form-label">Tipe User</label>
                <input type="text" class="form-control" id="user_type" name="user_type"
                    value="{{ old('user_type', $record->user_type) }}">
            </div>

            <div class="mb-3">
                <label for="cost_code" class="form-label">Cost Code</label>
                <input type="text" class="form-control" id="cost_code" name="cost_code"
                    value="{{ old('cost_code', $record->cost_code) }}">
            </div>

            <div class="mb-3">
                <label for="license_type" class="form-label">Tipe Lisensi</label>
                <input type="text" class="form-control" id="license_type" name="license_type"
                    value="{{ old('license_type', $record->license_type) }}">
            </div>

            <div class="mb-3">
                <label for="valid_from" class="form-label">Valid From</label>
                <input type="date" class="form-control" id="valid_from" name="valid_from"
                    value="{{ old('valid_from', $record->valid_from) }}">
            </div>

            <div class="mb-3">
                <label for="valid_to" class="form-label">Valid To</label>
                <input type="date" class="form-control" id="valid_to" name="valid_to"
                    value="{{ old('valid_to', $record->valid_to) }}">
            </div>

            <div class="mb-3">
                <label for="last_login" class="form-label">Last Login</label>
                <input type="datetime-local" class="form-control" id="last_login" name="last_login"
                    value="{{ old('last_login', $record->last_login ? \Carbon\Carbon::parse($record->last_login)->format('Y-m-d\TH:i') : '') }}">
            </div>

            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control" id="keterangan" name="keterangan">{{ old('keterangan', $record->keterangan) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('user-system.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
