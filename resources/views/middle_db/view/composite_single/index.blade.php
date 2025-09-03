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
                <h5 class="mb-0">Composite - Single Role</h5>
                <div class="d-flex gap-2">
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
                </div>
            </div>
            <div class="card-body">
                <table id="tblCompositeSingle" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Composite Role</th>
                            <th>Single Role</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" class="form-control form-control-sm" placeholder="Composite"></th>
                            <th><input data-col="1" class="form-control form-control-sm" placeholder="Single"></th>
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

            const $compInput = $('#tblCompositeSingle thead tr.filters input[data-col="0"]');
            const $singleInput = $('#tblCompositeSingle thead tr.filters input[data-col="1"]');

            const table = $('#tblCompositeSingle').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25, 50, 100],
                    [5, 10, 25, 50, 100]
                ],
                order: [
                    [0, 'asc']
                ],
                orderCellsTop: true,
                ajax: {
                    url: '{{ route('middle_db.view.uam.composite_single.data') }}',
                    data: function(d) {
                        // Pass composite & single filters separately (pagination stays on composites)
                        d.comp = $compInput.val();
                        d.single = $singleInput.val();
                    }
                },
                columns: [{
                        data: 'composite_role',
                        name: 'composite_role'
                    },
                    {
                        data: 'single_role',
                        name: 'single_role'
                    }
                ],
                language: {
                    lengthMenu: 'Show _MENU_ composites',
                    info: ''
                },
                infoCallback: function(settings, start, end, max, total /* pre */ ) {
                    // start/end are composite offsets because we treat pagination that way.
                    const api = this.api();
                    const pageData = api.rows({
                        page: 'current'
                    }).data();
                    const compsOnPage = {};
                    pageData.each(r => compsOnPage[r.group_key] = true);
                    const pageCompositeCount = Object.keys(compsOnPage).length;
                    // DataTables' start is composite offset
                    const compositeStart = settings._iDisplayStart + 1;
                    const compositeEnd = compositeStart + pageCompositeCount - 1;
                    const totalFilteredComposites = settings._iRecordsDisplay;
                    const singleRows = pageData.length;
                    if (totalFilteredComposites === 0) {
                        return 'No composites found';
                    }
                    return `Showing composites ${compositeStart}-${compositeEnd} of ${totalFilteredComposites} (displaying ${singleRows} single roles)`;
                },
                drawCallback: function() {
                    applyRowSpans(this.api(), 0);
                }
            });

            // Trigger redraw on filter change
            $compInput.add($singleInput).on('keyup change', function() {
                table.draw();
            });

            document.getElementById('btnReload')?.addEventListener('click', () => {
                $compInput.val('');
                $singleInput.val('');
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
