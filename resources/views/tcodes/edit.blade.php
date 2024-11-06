@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Tcode</h1>

        <form action="{{ route('tcodes.update', $tcode->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Nama Perusahaan</label>
                <select name="company_id" id="company_id" class="form-control select2">
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ $company->id == $tcode->company_id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tcode Code Field -->
            <div class="mb-3">
                <label for="code" class="form-label">Nama Tcode</label>
                <input type="text" class="form-control" name="code" value="{{ $tcode->code }}" required>
            </div>

            <!-- Tcode Description Field -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="deskripsi">{{ $tcode->deskripsi }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Tcode</button>
        </form>
    </div>
@endsection
