@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @php
            $titles = [
                'single_tcode' => 'Single Role - Tcode',
                'composite_single' => 'Composite Role - Single Role',
                'user_composite' => 'User - Composite Role',
            ];
            $title = $titles[$scope ?? ''] ?? 'Relationship Compare';
            $localOnly = $localOnly ?? [];
            $middleOnly = $middleOnly ?? [];
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
                        <span class="badge bg-primary">{{ count($localOnly) }}</span>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Filter..."
                            data-dt-filter data-target-table="local-only-table">
                        <div class="table-responsive">
                            <table id="local-only-table" class="table table-sm table-striped table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Company</th>
                                        <th style="width: 240px;">{{ $leftLabel ?? 'Left' }}</th>
                                        <th style="width: 240px;">{{ $rightLabel ?? 'Right' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($localOnly as $row)
                                        <tr>
                                            <td>{{ $row['company'] ?? '' }}</td>
                                            <td><code>{{ $row['left'] ?? '' }}</code></td>
                                            <td><code>{{ $row['right'] ?? '' }}</code></td>
                                        </tr>
                                    @endforeach
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
                        <span class="badge bg-warning text-dark">{{ count($middleOnly) }}</span>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Filter..."
                            data-dt-filter data-target-table="middle-only-table">
                        <div class="table-responsive">
                            <table id="middle-only-table" class="table table-sm table-striped table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Company</th>
                                        <th style="width: 240px;">{{ $leftLabel ?? 'Left' }}</th>
                                        <th style="width: 240px;">{{ $rightLabel ?? 'Right' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($middleOnly as $row)
                                        <tr>
                                            <td>{{ $row['company'] ?? '' }}</td>
                                            <td><code>{{ $row['left'] ?? '' }}</code></td>
                                            <td><code>{{ $row['right'] ?? '' }}</code></td>
                                        </tr>
                                    @endforeach
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
                language: {
                    emptyTable: 'No data available',
                    zeroRecords: 'No matching records found'
                }
            };

            const localDT = $('#local-only-table').DataTable(options);
            const middleDT = $('#middle-only-table').DataTable(options);

            document.querySelectorAll('[data-dt-filter]').forEach(input => {
                const targetId = input.dataset.targetTable;
                const dt = $('#' + targetId).DataTable();
                input.addEventListener('input', () => dt.search(input.value).draw());
            });
        })();
    </script>
@endsection
