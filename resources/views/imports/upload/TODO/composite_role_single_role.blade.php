@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Upload Composite Role - Single Role Data</h1>
        <form action="{{ route('composite_role_single_role.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="excel_file" class="form-label">Upload Excel File</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Preview</button>
        </form>
    </div>
@endsection
