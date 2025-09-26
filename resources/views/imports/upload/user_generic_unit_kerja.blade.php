@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Upload User Generic Unit Kerja</h5>
                <a href="{{ route('user-generic-unit-kerja.downloadTemplate') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download"></i> Download Template
                </a>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Perbaiki input berikut:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('user-generic-unit-kerja.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="periode_id" class="form-label">Periode<span class="text-danger">*</span></label>
                            <select name="periode_id" id="periode_id" class="form-select" required>
                                <option value="">Pilih Periode</option>
                                @foreach ($periodes as $periode)
                                    <option value="{{ $periode->id }}" @selected(old('periode_id') == $periode->id)>
                                        {{ $periode->definisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="excel_file" class="form-label">Excel File<span class="text-danger">*</span></label>
                            <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx,.xls"
                                required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
