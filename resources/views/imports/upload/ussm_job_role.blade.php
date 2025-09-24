@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Upload User ID - Job Role</h5>
                <a href="{{ route('ussm-job-role.template') }}" class="btn btn-outline-success btn-sm">
                    Download Template
                </a>
            </div>

            <div class="card-body">
                {{-- Success message --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Error message --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('ussm-job-role.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="periode_id" class="form-label">Periode</label>
                        <select name="periode_id" id="periode_id" class="form-control" required>
                            <option value="">-- Pilih Periode --</option>
                            @foreach ($periodes as $periode)
                                <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Excel File</label>
                        <input type="file" name="excel_file" id="excel_file" class="form-control" required
                            accept=".xlsx,.xls">
                    </div>

                    <button type="submit" class="btn btn-primary">Preview</button>
                </form>
            </div>
        </div>
    </div>
@endsection
