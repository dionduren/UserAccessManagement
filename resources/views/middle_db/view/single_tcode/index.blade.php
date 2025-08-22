@extends('layouts.app')
@section('header-scripts')
    <style>
        td._grp-hidden {
            display: none;
        }

        table.table-sm td,
        table.table-sm th {
            padding: .4rem .5rem;
            vertical-align: top;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Single Role - Tcode</h5>
                <div>
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
                </div>
            </div>
            <div class="card-body">
                <table id="tblSingleTcode" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Single Role</th>
                            <th>Tcode</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" id="filterSingle" class="form-control form-control-sm"
                                    placeholder="Single Role"></th>
                            <th><input data-col="1" id="filterTcode" class="form-control form-control-sm"
                                    placeholder="Tcode"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const $single = $('#filterSingle');
            const $tcode = $('#filterTcode');

            const table = $('#tblSingleTcode').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                order: [
                    [0, 'asc']
                ],
                orderCellsTop: true,
                ajax: {
                    url: '{{ route('middle_db.uam.single_tcode.data') }}',
                    data: function(d) {
                        d.single = $single.val();
                        d.tcode = $tcode.val();
                    }
                },
                columns: [{
                        data: 'single_role',
                        name: 'single_role'
                    },
                    {
                        data: 'tcode',
                        name: 'tcode'
                    }
                ],
                language: {
                    lengthMenu: 'Show _MENU_ single roles',
                    info: ''
                },
                infoCallback: function(settings, start, end, max, total) {
                    const api = this.api();
                    const pageData = api.rows({
                        page: 'current'
                    }).data();
                    const singlesOnPage = {};
                    pageData.each(r => singlesOnPage[r.group_key] = true);
                    const pageSingleCount = Object.keys(singlesOnPage).length;
                    const singleStart = settings._iDisplayStart + 1;
                    const singleEnd = singleStart + pageSingleCount - 1;
                    const totalFilteredSingles = settings._iRecordsDisplay;
                    const tcodeRows = pageData.length;
                    if (totalFilteredSingles === 0) return 'No single roles found';
                    return `Showing single roles ${singleStart}-${singleEnd} of ${totalFilteredSingles} (displaying ${tcodeRows} tcodes)`;
                },
                drawCallback: function() {
                    applyRowSpans(this.api(), 0);
                }
            });

            $('#tblSingleTcode thead tr.filters input').on('keyup change', function() {
                table.draw();
            });

            document.getElementById('btnReload').addEventListener('click', () => {
                $single.val('');
                $tcode.val('');
                table.draw();
            });

            function applyRowSpans(api, colIndex) {
                let lastKey = null,
                    firstCell = null,
                    span = 1;
                api.rows({
                    page: 'current'
                }).every(function(rowIdx) {
                    const data = this.data();
                    const key = data.group_key;
                    const cell = $(api.cell(rowIdx, colIndex).node());
                    if (lastKey === key) {
                        cell.addClass('_grp-hidden');
                        span++;
                        if (firstCell) firstCell.attr('rowspan', span);
                    } else {
                        lastKey = key;
                        span = 1;
                        firstCell = cell;
                        cell.removeClass('_grp-hidden').attr('rowspan', 1);
                    }
                });
            }
        });
    </script>
@endsection
