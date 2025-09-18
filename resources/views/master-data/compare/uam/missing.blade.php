{{-- filepath: resources/views/master-data/compare/unit_kerja.blade.php --}}
@extends('layouts.app')

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
                        <span class="badge bg-primary">{{ count($localMissing) }}</span>
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
                                            <td colspan="4" class="text-center text-muted">No local-only differences.
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
                        <span class="badge bg-warning text-dark">{{ count($middleMissing) }}</span>
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
                                            <td colspan="4" class="text-center text-muted">No middle-only differences.
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

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.8/datatables.min.js"></script>
    <script>
        (function() {
            const options = {
                lengthMenu: [25, 50, 100],
                pageLength: 25,
                order: [
                    [0, 'asc'],
                    [1, 'asc'],
                    [2, 'asc']
                ],
            };

            const localDT = $('#local-missing-table').DataTable(options);
            const middleDT = $('#middle-missing-table').DataTable(options);

            document.querySelectorAll('[data-dt-filter]').forEach(input => {
                const targetId = input.dataset.targetTable;
                const dt = $('#' + targetId).DataTable();
                input.addEventListener('input', () => dt.search(input.value).draw());
            });
        })();
    </script>
@endsection
