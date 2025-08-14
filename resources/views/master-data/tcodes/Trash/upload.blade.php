@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Upload Tcode Excel File</h1>

        <!-- Button to Download Template -->
        <a href="{{ route('tcodes.download-template') }}" class="btn btn-secondary mb-3">
            <i class="bi bi-download"></i> Download Template
        </a>

        <!-- Upload Form -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tcodes.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="excel_file" class="form-label">Select Excel File</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload and Preview</button>
        </form>
    </div>
@endsection
