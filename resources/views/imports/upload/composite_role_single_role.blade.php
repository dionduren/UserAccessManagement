{{-- @extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Upload Composite & Single Roles</h1>

        <!-- Display errors, if any -->
        @if ($errors->any())
            <div class="alert alert-warning">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            error 1
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('validationErrors'))
            <div class="alert alert-danger">
                <h4>Validation Errors:</h4>
                <ul>
                    @foreach (session('validationErrors') as $row => $messages)
                        <li><strong>Row {{ $row }}:</strong>
                            <ul>
                                @foreach ($messages as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Display success message, if any -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Upload Form -->
        <form action="{{ route('composite_single.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="excel_file">Select Excel File</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xls,.xlsx" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Preview</button>
        </form>
    </div>
@endsection --}}

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Upload Composite & Single Roles</h1>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Display error messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('composite_single.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="excel_file" class="form-label">Upload Excel File</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Preview</button>
        </form>
    </div>
@endsection
