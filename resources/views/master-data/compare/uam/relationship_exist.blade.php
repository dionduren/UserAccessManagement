@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @php
            $titles = [
                'single_tcode' => 'Single Role - Tcode',
                'composite_single' => 'Composite Role - Single Role',
                'user_composite' => 'User - Composite Role',
            ];
            $title = $titles[$scope ?? ''] ?? 'Relationship';
            $rows = $rows ?? [];
        @endphp

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Existing in both: {{ $title }}</h4>
            <span class="badge bg-secondary">{{ now()->format('Y-m-d H:i') }}</span>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="tblExistUamRel" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Company</th>
                            <th style="width: 240px;">{{ $leftLabel ?? 'Left' }}</th>
                            <th style="width: 240px;">{{ $rightLabel ?? 'Right' }}</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" class="form-control form-control-sm" placeholder="Company"></th>
                            <th><input data-col="1" class="form-control form-control-sm"
                                    placeholder="{{ $leftLabel ?? 'Left' }}"></th>
                            <th><input data-col="2" class="form-control form-control-sm"
                                    placeholder="{{ $rightLabel ?? 'Right' }}"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td>{{ $r['company'] ?? '' }}</td>
                                <td><code>{{ $r['left'] ?? '' }}</code></td>
                                <td><code>{{ $r['right'] ?? '' }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">Scope: {{ $scope }}</div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.8/datatables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = $('#tblExistUamRel').DataTable({
                pageLength: 25,
                lengthMenu: [25, 50, 100],
                orderCellsTop: true,
                order: [
                    [0, 'asc'],
                    [1, 'asc'],
                    [2, 'asc']
                ],
                language: {
                    emptyTable: 'No data available',
                    zeroRecords: 'No matching records found'
                }
            });
            $('#tblExistUamRel thead tr.filters input').on('keyup change', function() {
                const c = $(this).data('col'),
                    v = this.value;
                if (table.column(c).search() !== v) table.column(c).search(v).draw();
            });
        });
    </script>
@endsection
