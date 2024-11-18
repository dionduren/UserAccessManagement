@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Tcode</h1>

        <form action="{{ route('tcodes.store') }}" method="POST">
            @csrf

            <!-- Tcode Code Field -->
            <div class="mb-3">
                <label for="code" class="form-label">Nama Tcode</label>
                <input type="text" class="form-control" name="code" required>
            </div>

            <!-- Tcode SAP Module Field -->
            <div class="mb-3">
                <label for="sap_module" class="form-label">SAP Module</label>
                <input type="text" class="form-control" name="sap_module" required>
            </div>

            <!-- Tcode Description Field -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="deskripsi"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Tcode</button>
        </form>
    </div>
@endsection
