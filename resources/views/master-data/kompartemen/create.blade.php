@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Buat Master Data Kompartemen Baru</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('kompartemens.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="company_id" class="form-label">Perusahaan</label>
                <select name="company_id" class="form-control" required>
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->company_code }}"
                            {{ old('company_id') == $company->company_code ? 'selected' : '' }}>
                            {{ $company->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kode Kompartemen</label>
                <input type="text" class="form-control" name="kompartemen_id" value="{{ old('kompartemen_id') }}"
                    required>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Nama Kompartemen</label>
                <input type="text" class="form-control" name="nama" value="{{ old('nama') }}" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="deskripsi">{{ old('deskripsi') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Buat Kompartemen</button>
        </form>
    </div>
@endsection
