@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Upload Company-Kompartemen Data</h1>

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

        <form action="{{ route('company_kompartemen.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="excel_file" class="form-label">Upload Excel File</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Preview</button>
        </form>
    </div>
@endsection
