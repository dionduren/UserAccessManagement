@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Upload User NIK Excel File</h1>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Debug all session data --}}
        {{-- <pre>{{ print_r(session()->all(), true) }}</pre>

        @php
            $parsedData = session('parsedData', []);
        @endphp --}}

        @if ($errors->has('excel_errors'))
            <div class="alert alert-danger">
                <strong>Excel Import Errors:</strong>
                <ul>
                    @foreach ($errors->get('excel_errors') as $rowIndex => $rowErrors)
                        <li>Row {{ $rowIndex }}:
                            <ul>
                                @foreach ($rowErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Button to Download Template -->
        <a href="{{ route('user-nik.download-template') }}" class="btn btn-secondary mb-3">
            <i class="bi bi-download"></i> Download Template
        </a>

        <!-- Upload Form -->
        <form action="{{ route('user-nik.upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="periode_id" class="form-label">Pilih Periode</label>
                <select name="periode_id" id="periode_id" class="form-control form-select" required>
                    <option value="">Silahkan Pilih Periode Data</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="excel_file" class="form-label">Pilih File Excel</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" required>

                {{-- Loading Spiner --}}
                <div class="d-flex justify-content-center mt-3" id="loading-spinner" style="display: none !important;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

            </div>
            <button type="submit" class="btn btn-primary" id="upload-btn">Upload and Preview</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#excel_file').change(function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('label').text('File yang Diupload = ' + fileName);
            });

            $('#upload-btn').click(function(e) {
                if ($('#periode_id').val() === '') {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error',
                        text: 'Periode harus diisi',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else if ($('#excel_file')[0].files.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error',
                        text: 'File excel harus diisi',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                } else {
                    $('#loading-spinner').show();
                }
            });
        });
    </script>
@endsection
