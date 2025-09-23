{{-- filepath: resources/views/master-data/compare/unit_kerja.blade.php --}}
@extends('layouts.app')

@section('styles')
    <!-- DataTables Core + Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Buttons Extension -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
    <div class="container-fluid">
        @php
            $labels = [
                'company' => 'Company',
                'kompartemen' => 'Kompartemen',
                'departemen' => 'Departemen',
                'cost_center' => 'Cost Center',
                'composite_role' => 'Composite Role',
                'single_role' => 'Single Role',
                'tcode' => 'Tcode',
            ];
            $title = $labels[$scope ?? ''] ?? ucfirst(str_replace('_', ' ', $scope ?? 'Compare'));
            $localMissing = $localMissing ?? [];
            $middleMissing = $middleMissing ?? [];
        @endphp

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Compare: {{ $title }}</h4>
            <span class="badge bg-secondary">{{ now()->format('Y-m-d H:i') }}</span>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Local only</strong>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('compare.uam.export', [$scope, 'local']) }}"
                                class="btn btn-sm btn-outline-success">
                                Excel
                            </a>
                            <span class="badge bg-primary">{{ count($localMissing) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Filter..."
                            data-dt-filter data-target-table="local-missing-table">
                        <div class="table-responsive">
                            <table id="local-missing-table"
                                class="table table-sm table-striped table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Company</th>
                                        {{-- <th style="width: 140px;">Level</th> --}}
                                        <th style="width: 220px;">ID</th>
                                        <th>Description / Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($localMissing as $row)
                                        <tr>
                                            <td>{{ $row['company'] ?? '' }}</td>
                                            {{-- <td>{{ $row['level'] ?? '' }}</td> --}}
                                            <td><code>{{ $row['id'] ?? '' }}</code></td>
                                            <td>{{ $row['value'] ?? '' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No local-only differences.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted small">
                        Scope: {{ $scope }}
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Middle only</strong>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('compare.uam.export', [$scope, 'middle']) }}"
                                class="btn btn-sm btn-outline-success">
                                Excel
                            </a>
                            <span class="badge bg-warning text-dark">{{ count($middleMissing) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Filter..."
                            data-dt-filter data-target-table="middle-missing-table">
                        <div class="table-responsive">
                            <table id="middle-missing-table"
                                class="table table-sm table-striped table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Company</th>
                                        {{-- <th style="width: 140px;">Level</th> --}}
                                        <th style="width: 220px;">ID</th>
                                        <th>Description / Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($middleMissing as $row)
                                        <tr>
                                            <td>{{ $row['company'] ?? '' }}</td>
                                            {{-- <td>{{ $row['level'] ?? '' }}</td> --}}
                                            <td><code>{{ $row['id'] ?? '' }}</code></td>
                                            <td>{{ $row['value'] ?? '' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No middle-only differences.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted small">
                        Scope: {{ $scope }}
                    </div>
                </div>
            </div>
        </div>

        @if (empty($localMissing) && empty($middleMissing))
            <div class="alert alert-success mt-3 mb-0">
                No differences detected for {{ $title }}.
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <!-- jQuery (pastikan hanya sekali di layout; hapus jika sudah ada) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables Core + Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Buttons + dependencies -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
@endpush

@section('scripts')
    <script>
        (function() {
            if (typeof $.fn.DataTable === 'undefined') {
                console.error('DataTables core belum termuat.');
                return;
            }
            if (typeof $.fn.dataTable.Buttons === 'undefined') {
                console.error('DataTables Buttons plugin belum termuat.');
            }

            function init(sel, side) {
                if ($.fn.DataTable.isDataTable(sel)) return;
                $(sel).DataTable({
                    autoWidth: false,
                    deferRender: true,
                    pageLength: 25,
                    lengthMenu: [25, 50, 100],
                    order: [
                        [0, 'asc']
                    ],
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Filtered',
                        className: 'btn btn-sm btn-outline-secondary',
                        filename: function() {
                            return 'FILTERED_{{ strtoupper($scope) }}_' + side + '_' + (new Date())
                                .toISOString().replace(/[:\-T]/g, '').slice(0, 14);
                        },
                        title: null,
                        exportOptions: {
                            columns: [0, 1, 2],
                            modifier: {
                                search: 'applied',
                                order: 'applied'
                            }
                        }
                    }],
                    columnDefs: [{
                        targets: '_all',
                        defaultContent: ''
                    }]
                });
            }

            init('#local-missing-table', 'LOCAL');
            init('#middle-missing-table', 'MIDDLE');

            document.querySelectorAll('[data-dt-filter]').forEach(inp => {
                inp.addEventListener('input', () => {
                    const tableSel = '#' + inp.dataset.targetTable;
                    if ($.fn.DataTable.isDataTable(tableSel)) {
                        $(tableSel).DataTable().search(inp.value).draw();
                    }
                });
            });
        })();
    </script>
@endsection
