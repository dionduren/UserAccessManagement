@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Periode</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('periode.update', $periode->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="definisi" class="form-label">Periode</label>
                <input type="text" id="definisi" class="form-control @error('definisi') is-invalid @enderror"
                    name="definisi" value="{{ old('definisi', $periode->definisi) }}" required>
                @error('definisi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="tanggal_create_periode" class="form-label">Tanggal Buat Periode</label>
                <input type="datetime-local" id="tanggal_create_periode"
                    class="form-control @error('tanggal_create_periode') is-invalid @enderror" name="tanggal_create_periode"
                    value="{{ old('tanggal_create_periode', $periode->tanggal_create_periode ? \Carbon\Carbon::parse($periode->tanggal_create_periode)->format('Y-m-d\TH:i') : '') }}"
                    required>
                @error('tanggal_create_periode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Status Aktif</label>
                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                    <option value="1" {{ old('is_active', $periode->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active', $periode->is_active) == 0 ? 'selected' : '' }}>Tidak Aktif
                    </option>
                </select>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Ubah Periode</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script></script>
@endsection
