@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Detail Penomoran UAR</h1>

        <div class="card">
            <div class="card-header">
                Penomoran UAR #{{ $penomoranUAR->id }}
            </div>
            <div class="card-body">
                <p><strong>Company ID:</strong> {{ $penomoranUAR->company_id }} -{{ $penomoranUAR->company->nama }}</p>
                <p><strong>Kompartemen ID:</strong> {{ $penomoranUAR->kompartemen_id }}
                    -{{ $penomoranUAR->kompartemen->nama }}</p>
                <p><strong>Departemen ID:</strong>
                    {{ $penomoranUAR->departemen_id ? $penomoranUAR->departemen_id . ' - ' . $penomoranUAR->departemen->nama : 'KOSONG' }}
                </p>
                {{-- <p><strong>Unit Kerja ID:</strong> {{ $penomoranUAR->unit_kerja_id }}</p> --}}
                <p><strong>Number:</strong> {{ $penomoranUAR->number }}</p>
            </div>
            <div class="card-footer">
                <a href="{{ route('penomoran-uar.index') }}" class="btn btn-primary">Back to List</a>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Any additional JavaScript for this view can go here
        });
    </script>
@endsection
