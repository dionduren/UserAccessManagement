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
                <h5 class="mb-0">Composite - Single Role (AO Suffix)</h5>
                <div class="d-flex gap-2">
                    <button id="btnSync" class="btn btn-primary btn-sm">
                        <span class="me-1">Sync</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
                </div>
            </div>
            <div class="card-body">
                <table id="tblCompositeSingleAO" class="table table-sm table-bordered table-striped w-100">
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

            // Sync button logic (merged here)
            const btnSync = document.getElementById('btnSync');
            if (btnSync) {
                btnSync.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Synchronize?',
                        text: 'Run composite AO synchronization now.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, sync',
                        cancelButtonText: 'Cancel'
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        const spinner = btnSync.querySelector('.spinner-border');
                        btnSync.disabled = true;
                        spinner.classList.remove('d-none');

                        fetch(`{{ route('import.uam.composite_ao') }}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.ok ? r.json() : r.json().catch(() => ({})).then(j =>
                                Promise.reject(j)))
                            .then(data => {
                                Swal.fire({
                                    icon: data.status ? 'success' : 'error',
                                    title: data.status ? 'Synchronization Complete' :
                                        'Synchronization Failed',
                                    text: data.message || (data.status ?
                                        'Data successfully synchronized.' :
                                        'Failed to synchronize data.')
                                });
                                if (data.status) document.getElementById('btnReload')?.click();
                            })
                            .catch(err => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: err.message || err.error ||
                                        'Unexpected error occurred.'
                                });
                            })
                            .finally(() => {
                                btnSync.disabled = false;
                                spinner.classList.add('d-none');
                            });
                    });
                });
            }

            // DataTable logic
            const $compInput = $('#tblCompositeSingleAO thead tr.filters input[data-col="0"]');
            const $singleInput = $('#tblCompositeSingleAO thead tr.filters input[data-col="1"]');

            const table = $('#tblCompositeSingleAO').DataTable({
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
                    url: '{{ route('middle_db.view.uam.composite_ao.data') }}',
                    data: d => {
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
                    lengthMenu: 'Show _MENU_ AO composites',
                    info: ''
                },
                infoCallback: function(settings) {
                    const api = this.api();
                    const pageData = api.rows({
                        page: 'current'
                    }).data();
                    const comps = {};
                    pageData.each(r => comps[r.group_key] = true);
                    const pageCompositeCount = Object.keys(comps).length;
                    const compositeStart = settings._iDisplayStart + 1;
                    const compositeEnd = compositeStart + pageCompositeCount - 1;
                    const totalFiltered = settings._iRecordsDisplay;
                    const singleRows = pageData.length;
                    if (totalFiltered === 0) return 'No AO composites found';
                    return `Showing AO composites ${compositeStart}-${compositeEnd} of ${totalFiltered} (displaying ${singleRows} single roles)`;
                },
                drawCallback: function() {
                    applyRowSpans(this.api(), 0);
                }
            });

            $compInput.add($singleInput).on('keyup change', () => table.draw());

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
