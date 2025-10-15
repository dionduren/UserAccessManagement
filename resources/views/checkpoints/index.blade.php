@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Checkpoint Progress</h5>
                <div class="d-flex gap-2 align-items-center">
                    <form method="GET" action="{{ route('checkpoints.index') }}" class="d-flex">
                        <select id="periode-select" name="periode_id" class="form-select form-select-sm"
                            onchange="this.form.submit()">
                            @foreach ($periodes as $periode)
                                <option value="{{ $periode->id }}"
                                    {{ $periode->id == $selectedPeriode ? 'selected' : '' }}>
                                    {{ $periode->periode ?? ($periode->definisi ?? 'Periode ' . $periode->id) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    <form id="refresh-form" method="POST" action="{{ route('checkpoints.refresh') }}">
                        @csrf
                        <input type="hidden" name="periode_id" value="{{ $selectedPeriode }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            Check Progress
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive table-sticky-header">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light align-top">
                            <tr>
                                <th class="w-25 sticky-col">Step</th>
                                @foreach ($companies as $company)
                                    <th class="company-col company-header-col">{{ $company->nama }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($steps as $stepKey => $label)
                                <tr>
                                    <td class="fw-semibold align-top sticky-col">{!! $label !!}</td>
                                    @foreach ($companies as $company)
                                        @php
                                            $cell = $matrix[$stepKey][$company->company_code] ?? null;
                                            $status = $cell['status'] ?? 'pending';
                                            $badgeClass = match ($status) {
                                                'completed' => 'bg-success',
                                                'in_progress' => 'bg-warning text-dark',
                                                'failed' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <td class="text-center align-top company-col">
                                            <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                            @if (!empty($cell['completed_at']))
                                                <div class="small text-muted mt-1">
                                                    {{ $cell['completed_at']->format('d M Y H:i') }}
                                                </div>
                                            @endif
                                            @if (!empty($cell['payload']['summary']))
                                                <div class="small mt-1">{!! $cell['payload']['summary'] !!}</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 small text-muted">
                    *Status default: Pending (belum diproses), Completed (selesai), Failed (gagal – periksa log).
                </div>
            </div>
        </div>
    </div>
@endsection

@section('header-scripts')
    <style>
        /* Scroll container; keep header/first column sticky */
        .table-sticky-header {
            overflow-y: auto;
            overflow-x: auto;
            max-height: 70vh;
        }

        /* Sticky header */
        .table-sticky-header thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #f8f9fa;
        }

        /* Sticky first column (header + body) */
        .table-sticky-header .sticky-col {
            position: sticky;
            left: 0;
            z-index: 3;
            background-color: #fff;
            box-shadow: 2px 0 0 rgba(0, 0, 0, .05);
        }

        /* Top-left cell */
        .table-sticky-header thead .sticky-col {
            z-index: 4;
            background-color: #f8f9fa;
            box-shadow: 2px 0 0 rgba(0, 0, 0, .05), 0 2px 0 rgba(0, 0, 0, .05);
        }

        /* Widen company columns */
        .table-sticky-header th.company-col,
        .table-sticky-header td.company-col {
            min-width: 150px;
            vertical-align: top;
        }

        /* Center + middle only for company header cells */
        .table-sticky-header thead th.company-header-col {
            text-align: center;
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('periode-select');
            const hidden = document.querySelector('#refresh-form input[name="periode_id"]');
            select.addEventListener('change', () => hidden.value = select.value);

            // Adjust scrollable height to viewport so header stays visible on zoom/resize
            const container = document.querySelector('.table-sticky-header');

            function adjustMaxHeight() {
                if (!container) return;
                const rect = container.getBoundingClientRect();
                const bottomPadding = 24; // space to card bottom
                const max = window.innerHeight - rect.top - bottomPadding;
                container.style.maxHeight = (max > 200 ? max : 200) + 'px';
            }

            // Initial and on resize (zoom triggers resize too)
            adjustMaxHeight();
            window.addEventListener('resize', adjustMaxHeight);
        });
    </script>
@endsection
