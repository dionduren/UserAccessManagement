@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Upload Modul - {{ ucfirst(config('dynamic_uploads.modules.' . $module . '.name')) }}</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('dynamic_upload.handleUpload', ['module' => $module]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label>Periode:</label>

                <select name="periode_id" class="form-control" required>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Upload Excel File:</label>
                <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
            </div>

            <button type="submit" class="btn btn-primary">Upload & Preview</button>
        </form>
    </div>
@endsection
