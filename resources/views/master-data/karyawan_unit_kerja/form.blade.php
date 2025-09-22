@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">{{ $title }}</h1>
                    </div>
                    <div class="card-body">

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ $route }}" method="POST">
                            @csrf
                            @if ($method === 'PUT')
                                @method('PUT')
                            @endif

                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label">Company *</label>
                                    <select name="company" class="form-select" required>
                                        <option value="">--</option>
                                        @foreach ($companies as $code => $label)
                                            <option value="{{ $code }}" @selected(old('company', $model->company) === $code)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Direktorat ID</label>
                                    <input type="text" name="direktorat_id"
                                        value="{{ old('direktorat_id', $model->direktorat_id) }}" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Direktorat</label>
                                    <input type="text" name="direktorat"
                                        value="{{ old('direktorat', $model->direktorat) }}" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Kompartemen</label>
                                    <select name="kompartemen_id" class="form-select">
                                        <option value="">--</option>
                                        @foreach ($kompartemen as $id => $label)
                                            <option value="{{ $id }}" @selected((string) old('kompartemen_id', (string) $model->kompartemen_id) == (string) $id)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Departemen</label>
                                    <select name="departemen_id" class="form-select">
                                        <option value="">--</option>
                                        @foreach ($departemen as $id => $label)
                                            <option value="{{ $id }}" @selected((string) old('departemen_id', (string) $model->departemen_id) == (string) $id)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Atasan</label>
                                    <input type="text" name="atasan" value="{{ old('atasan', $model->atasan) }}"
                                        class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Cost Center</label>
                                    <input type="text" name="cost_center"
                                        value="{{ old('cost_center', $model->cost_center) }}" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">NIK *</label>
                                    <input type="text" name="nik" value="{{ old('nik', $model->nik) }}"
                                        class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Nama *</label>
                                    <input type="text" name="nama" value="{{ old('nama', $model->nama) }}"
                                        class="form-control" required>
                                </div>

                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button class="btn btn-primary"
                                    type="submit">{{ $method === 'PUT' ? 'Update' : 'Create' }}</button>
                                <a href="{{ route('karyawan_unit_kerja.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
