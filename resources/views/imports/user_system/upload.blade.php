@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Import User System</h5>
                <a href="{{ route('user_system.import.template') }}" class="btn btn-sm btn-success">Download Template</a>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger mb-2">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger mb-2">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('user_system.import.preview') }}" method="POST" enctype="multipart/form-data"
                    id="uploadForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Periode</label>
                            <select name="periode_id" id="periode_id" class="form-select" required>
                                <option value="">-- Pilih Periode --</option>
                                @foreach ($periodes as $p)
                                    <option value="{{ $p->id }}">{{ $p->definisi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">File (xlsx)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100">Preview</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Initialize select2 if available
            if ($.fn.select2) {
                $('#periode_id').select2({
                    placeholder: 'Pilih Periode',
                    width: '100%'
                });
            }
        });
    </script>
@endsection
