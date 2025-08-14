@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Upload User Generic Excel</h2>
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('user-generic.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="periode_id">Periode</label>
                <select name="periode_id" id="periode_id" class="form-control" required>
                    <option value="">-- Select Periode --</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mt-2">
                <label for="excel_file">Excel File</label>
                <input type="file" name="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Preview</button>
        </form>
        @if (session('validationErrors'))
            <div class="alert alert-danger mt-2">
                <ul>
                    @foreach (session('validationErrors') as $row => $errors)
                        <li>Row {{ $row }}: {{ implode(', ', $errors) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
