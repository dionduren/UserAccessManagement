@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @php
            $labels = [
                'composite_role' => 'Composite Role',
                'single_role' => 'Single Role',
                'tcode' => 'Tcode',
            ];
            $title = $labels[$scope ?? ''] ?? 'UAM';
            $rows = $rows ?? [];
        @endphp

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Existing in both: {{ $title }}</h4>
            <span class="badge bg-secondary">{{ now()->format('Y-m-d H:i') }}</span>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="tblExistUam" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Company</th>
                            {{-- <th style="width: 140px;">Level</th> --}}
                            <th style="width: 240px;">ID</th>
                            <th>Description / Value</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" class="form-control form-control-sm" placeholder="Company"></th>
                            {{-- <th><input data-col="1" class="form-control form-control-sm" placeholder="Level"></th> --}}
                            <th><input data-col="1" class="form-control form-control-sm" placeholder="ID"></th>
                            <th><input data-col="2" class="form-control form-control-sm" placeholder="Description"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td>{{ $r['company'] ?? '' }}</td>
                                {{-- <td>{{ $r['level'] ?? '' }}</td> --}}
                                <td><code>{{ $r['id'] ?? '' }}</code></td>
                                <td>{{ $r['value'] ?? '' }}</td>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = $('#tblExistUam').DataTable({
                pageLength: 25,
                lengthMenu: [25, 50, 100],
                orderCellsTop: true,
                order: [
                    [0, 'asc'],
                    [1, 'asc'],
                ],
                language: {
                    emptyTable: 'No data available',
                    zeroRecords: 'No matching records found'
                }
            });
            $('#tblExistUam thead tr.filters input').on('keyup change', function() {
                const c = $(this).data('col'),
                    v = this.value;
                if (table.column(c).search() !== v) table.column(c).search(v).draw();
            });
        });
    </script>
@endsection
