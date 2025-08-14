@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Detail Penomoran UAR</h1>

        <div class="card">
            <div class="card-header">
                Penomoran UAM #{{ $penomoranUAM->id }}
            </div>
            <div class="card-body">
                <p><strong>Company ID:</strong> {{ $penomoranUAM->company_id }} -{{ $penomoranUAM->company->nama }}</p>
                <p><strong>Kompartemen ID:</strong> {{ $penomoranUAM->kompartemen_id }}
                    -{{ $penomoranUAM->kompartemen->nama }}</p>
                <p><strong>Departemen ID:</strong>
                    {{ $penomoranUAM->departemen_id ? $penomoranUAM->departemen_id . ' - ' . $penomoranUAM->departemen->nama : 'KOSONG' }}
                </p>
                {{-- <p><strong>Unit Kerja ID:</strong> {{ $penomoranUAM->unit_kerja_id }}</p> --}}
                <p><strong>Number:</strong> {{ $penomoranUAM->number }}</p>
            </div>
            <div class="card-footer">
                <a href="{{ route('penomoran-uam.index') }}" class="btn btn-primary">Back to List</a>
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
