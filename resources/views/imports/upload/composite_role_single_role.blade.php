@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">Upload Composite & Single Roles</h1>
                    </div>
                    <div class="card-body">

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

                        <a href="{{ route('composite_single.template') }}" target="_blank" class="btn btn-success mb-3">
                            Download Template Composite Role - Single Role
                        </a>


                        <form action="{{ route('composite_single.preview') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Upload Excel File</label>
                                <input type="file" name="excel_file" id="excel_file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Preview</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
