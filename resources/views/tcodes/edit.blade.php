@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Tcode</h1>

        <form action="{{ route('tcodes.update', $tcode->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Tcode Code Field -->
            <div class="mb-3">
                <label for="code" class="form-label">Nama Tcode</label>
                <input type="text" class="form-control" name="code" value="{{ $tcode->code }}" required>
            </div>

            <!-- Tcode SAP Module Field -->
            <div class="mb-3">
                <label for="sap_module" class="form-label">Modul SAP</label>
                <input type="text" class="form-control" name="sap_module" value="{{ $tcode->code }}" required>
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
