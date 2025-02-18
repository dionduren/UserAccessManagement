@extends('layouts.app')

@section('content')
    <div style="padding-top: 30px; padding-inline-start: 30px;">
        <h1>Upload Single Roles & Tcode</h1>

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



        @if (session('validationErrors'))
            <div class="alert alert-danger">
                <h4>Validation Errors:</h4>
                <ul>
                    @foreach (session('validationErrors') as $row => $messages)
                        <li><strong>Row {{ $row }}:</strong>
                            <ul>
                                @foreach ($messages as $message)
                                    @if (is_array($message))
                                        @foreach ($message as $subMessage)
                                            <li>{{ $subMessage }}</li>
                                        @endforeach
                                    @else
                                        <li>{{ $message }}</li>
                                    @endif
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

        <form action="{{ route('tcode_single_role.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="excel_file" class="form-label">Upload Excel File</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Preview</button>
        </form>
    </div>
@endsection
