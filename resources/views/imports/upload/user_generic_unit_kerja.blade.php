@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Upload User Generic Unit Kerja Excel</h2>
        <form action="{{ route('user-generic-unit-kerja.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="periode_id" class="form-label">Periode</label>
                <select name="periode_id" id="periode_id" class="form-control" required>
                    <option value="">Pilih Periode</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="excel_file" class="form-label">Excel File</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Preview</button>
        </form>
    </div>
@endsection
