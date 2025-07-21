@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit User Generic</h2>
        <form action="{{ route('user-generic.update', $userGeneric->id) }}" method="POST">
            @csrf
            @method('PUT')


            <div class="mb-3">
                <label for="periode_id" class="form-label">Periode</label>
                <select class="form-control" id="periode_id" name="periode_id" required>
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}"
                            {{ old('periode_id', $userGeneric->periode_id) == $periode->id ? 'selected' : '' }}>
                            {{ $periode->definisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="group" class="form-label">Perusahaan</label>
                <select class="form-control" id="group" name="group">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->shortname }}"
                            {{ old('group', $userGeneric->Company->shortname) == $company->shortname ? 'selected' : '' }}>
                            {{ $company->company_code }} - {{ $company->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="user_code" class="form-label">User Code</label>
                <input type="text" class="form-control" id="user_code" name="user_code"
                    value="{{ old('user_code', $userGeneric->user_code) }}" required>
            </div>

            <div class="mb-3">
                <label for="user_type" class="form-label">Tipe User</label>
                <select class="form-control" id="user_type" name="user_type">
                    <option value="">-- Pilih Tipe User --</option>
                    <option value="internal" {{ old('user_type', $userGeneric->user_type) == 'Generic' ? 'selected' : '' }}>
                        Generic / Cost Center</option>
                    <option value="external" {{ old('user_type', $userGeneric->user_type) == 'NIK' ? 'selected' : '' }}>NIK
                    </option>
                </select>

                <div class="mb-3">
                    <label for="cost_code" class="form-label">Cost Code</label>
                    <input type="text" class="form-control" id="cost_code" name="cost_code"
                        value="{{ old('cost_code', $userGeneric->cost_code) }}">
                </div>

                <div class="mb-3">
                    <label for="license_type" class="form-label">Tipe Lisensi SAP</label>
                    <select class="form-control" id="license_type" name="license_type">
                        <option value="">-- Pilih Tipe Lisensi --</option>
                        @foreach ($licenseTypes as $type)
                            <option value="{{ $type->license_type }}"
                                {{ old('license_type', $userGeneric->license_type) == $type->license_type ? 'selected' : '' }}>
                                {{ $type->license_type }} - {{ $type->contract_license_type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="mb-3">
                <label for="pic" class="form-label">PIC</label>
                <input type="text" class="form-control" id="pic" name="pic"
                    value="{{ old('pic', $userGeneric->pic) }}">
            </div>

            <div class="mb-3">
                <label for="unit_kerja" class="form-label">Unit Kerja</label>
                <input type="text" class="form-control" id="unit_kerja" name="unit_kerja"
                    value="{{ old('unit_kerja', $userGeneric->unit_kerja) }}">
            </div> --}}

                {{-- <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kompartemen_id" class="form-label">Kompartemen ID</label>
                        <input type="text" class="form-control" id="kompartemen_id" name="kompartemen_id"
                            value="{{ old('kompartemen_id', $userGeneric->kompartemen_id) }}">
                    </div>
                    <div class="mb-3">
                        <label for="departemen_id" class="form-label">Departemen ID</label>
                        <input type="text" class="form-control" id="departemen_id" name="departemen_id"
                            value="{{ old('departemen_id', $userGeneric->departemen_id) }}">
                    </div>
                    <div class="mb-3">
                        <label for="job_role_id" class="form-label">Job Role ID</label>
                        <input type="text" class="form-control" id="job_role_id" name="job_role_id"
                            value="{{ old('job_role_id', $userGeneric->job_role_id) }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kompartemen_name" class="form-label">Kompartemen Name</label>
                        <input type="text" class="form-control" id="kompartemen_name" name="kompartemen_name"
                            value="{{ old('kompartemen_name', $userGeneric->kompartemen_name) }}">
                    </div>
                    <div class="mb-3">
                        <label for="departemen_name" class="form-label">Departemen Name</label>
                        <input type="text" class="form-control" id="departemen_name" name="departemen_name"
                            value="{{ old('departemen_name', $userGeneric->departemen_name) }}">
                    </div>
                    <div class="mb-3">
                        <label for="job_role_name" class="form-label">Job Role Name</label>
                        <input type="text" class="form-control" id="job_role_name" name="job_role_name"
                            value="{{ old('job_role_name', $userGeneric->job_role_name) }}">
                    </div>
                </div>
            </div> --}}

                {{-- <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control" id="keterangan" name="keterangan">{{ old('keterangan', $userGeneric->keterangan) }}</textarea>
            </div>

            <div class="mb-3">
                <label for="keterangan_update" class="form-label">Keterangan Update</label>
                <textarea class="form-control" id="keterangan_update" name="keterangan_update">{{ old('keterangan_update', $userGeneric->keterangan_update) }}</textarea>
            </div> --}}

                <div class="mb-3">
                    <label for="valid_from" class="form-label">Valid From</label>
                    <input type="date" class="form-control" id="valid_from" name="valid_from"
                        value="{{ old('valid_from', $userGeneric->valid_from) }}">
                </div>

                <div class="mb-3">
                    <label for="valid_to" class="form-label">Valid To</label>
                    <input type="date" class="form-control" id="valid_to" name="valid_to"
                        value="{{ old('valid_to', $userGeneric->valid_to) }}">
                </div>

                <div class="mb-3">
                    <label for="last_login" class="form-label">Last Login</label>
                    <input type="date" class="form-control" id="last_login" name="last_login"
                        value="{{ old('last_login', $userGeneric->last_login ? \Carbon\Carbon::parse($userGeneric->last_login)->format('Y-m-d') : '') }}">
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('user-generic.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
