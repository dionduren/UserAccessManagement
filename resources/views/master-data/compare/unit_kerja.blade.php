@extends('layouts.app')

@section('title', 'Compare Unit Kerja (Local vs Middle DB)')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">Perbandingan Data Unit Kerja</h4>

        <div class="row">
            <div class="col-1"></div>

            <div class="col-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <strong>Local Only (Not in Middle DB)</strong>
                        <span class="badge bg-primary">{{ count($localMissing ?? []) }} rows</span>
                    </div>
                    <div class="card-body">
                        <table id="local-missing-table" class="table table-bordered table-sm table-striped w-100">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Company</th>
                                    <th>Level</th>
                                    <th>ID</th>
                                    <th>Value (Nama)</th>
                                </tr>
                                <tr class="dt-filters">
                                    <th><input class="form-control form-control-sm" placeholder="Company"></th>
                                    <th></th>
                                    <th><input class="form-control form-control-sm" placeholder="Level"></th>
                                    <th><input class="form-control form-control-sm" placeholder="ID"></th>
                                    <th><input class="form-control form-control-sm" placeholder="Value"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($localMissing ?? [] as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row['company'] }}</td>
                                        <td>{{ $row['level'] }}</td>
                                        <td>{{ $row['id'] }}</td>
                                        <td>{{ $row['value'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (!count($localMissing ?? []))
                            <div class="alert alert-success mb-0">Tidak ada perbedaan data (Local).</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex align-items-center justify-content-center">
                <div class="vr" style="height:100%;"></div>
            </div>

            <div class="col-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <strong>Middle DB Only (Not in Local)</strong>
                        <span class="badge bg-primary">{{ count($middleMissing ?? []) }} rows</span>
                    </div>
                    <div class="card-body">
                        <table id="middle-missing-table" class="table table-bordered table-sm table-striped w-100">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Company</th>
                                    <th>Level</th>
                                    <th>ID</th>
                                    <th>Value (Nama)</th>
                                </tr>
                                <tr class="dt-filters">
                                    <th><input class="form-control form-control-sm" placeholder="Company"></th>
                                    <th></th>
                                    <th><input class="form-control form-control-sm" placeholder="Level"></th>
                                    <th><input class="form-control form-control-sm" placeholder="ID"></th>
                                    <th><input class="form-control form-control-sm" placeholder="Value"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($middleMissing ?? [] as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row['company'] }}</td>
                                        <td>{{ $row['level'] }}</td>
                                        <td>{{ $row['id'] }}</td>
                                        <td>{{ $row['value'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (!count($middleMissing ?? []))
                            <div class="alert alert-success mb-0">Tidak ada perbedaan data (Middle DB).</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        table.dataTable thead .dt-filters th {
            padding: 4px 6px !important;
            background: #f8f9fa;
        }

        .dt-filters input {
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function initDT(selector) {
                if (!document.querySelector(selector)) return;

                const table = $(selector).DataTable({
                    paging: true,
                    ordering: true,
                    info: true,
                    responsive: true,
                    lengthChange: true,
                    pageLength: 25,
                    deferRender: true,
                    order: [
                        [0, 'asc'],
                        [2, 'asc'],
                        [3, 'asc']
                    ], // Company, Level, ID
                    dom: 'lfrtip',
                    columnDefs: [{
                            targets: 1,
                            orderable: false
                        } // index column
                    ]
                });

                // Column filters
                $(selector + ' thead tr.dt-filters th').each(function(i) {
                    const input = $('input', this);
                    if (input.length) {
                        input.on('keyup change', function() {
                            if (table.column(i).search() !== this.value) {
                                table.column(i).search(this.value).draw();
                            }
                        });
                    }
                });

                function renumber() {
                    let start = table.page.info().start;
                    table.column(1, {
                        page: 'current'
                    }).nodes().each(function(cell, idx) {
                        cell.innerHTML = start + idx + 1;
                    });
                }
                table.on('order.dt search.dt page.dt draw.dt', renumber);
                renumber();
            }

            initDT('#local-missing-table');
            initDT('#middle-missing-table');
        });
    </script>
@endpush
