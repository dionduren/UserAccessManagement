@extends('layouts.app')

@section('title', 'USMM Master - All Users')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h2 class="mb-0 flex-grow-1">Middle DB - USMM Master (All Users)</h2>
                <form id="filterForm" class="d-flex gap-2 flex-wrap">
                    @csrf
                    {{-- Removed legacy global search (replaced by per-column filters) --}}
                    <button type="button" id="btnSync" class="btn btn-primary btn-sm">
                        Sync Data
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div id="syncStatus" class="small text-muted mb-2"></div>

                <table id="usmmTable" class="table table-sm table-striped table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>SAP User ID</th>
                            <th>Full Name</th>
                            <th>Department</th>
                            <th width='7.5%'>Last Logon Date</th>
                            <th width='7.5%'>Last Logon Time</th>
                            <th>User Type</th>
                            <th width='7.5%'>Valid From</th>
                            <th width='7.5%'>Valid To</th>
                            <th>Contractual Type</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" type="text" class="form-control form-control-sm"
                                    placeholder="Company"></th>
                            <th><input data-col="1" type="text" class="form-control form-control-sm"
                                    placeholder="User ID"></th>
                            <th><input data-col="2" type="text" class="form-control form-control-sm"
                                    placeholder="Full Name"></th>
                            <th><input data-col="3" type="text" class="form-control form-control-sm"
                                    placeholder="Department"></th>
                            <th><input data-col="4" type="text" class="form-control form-control-sm"
                                    placeholder="Logon Date"></th>
                            <th><input data-col="5" type="text" class="form-control form-control-sm"
                                    placeholder="Logon Time"></th>
                            <th><input data-col="6" type="text" class="form-control form-control-sm"
                                    placeholder="User Type"></th>
                            <th><input data-col="7" type="text" class="form-control form-control-sm"
                                    placeholder="Valid From"></th>
                            <th><input data-col="8" type="text" class="form-control form-control-sm"
                                    placeholder="Valid To"></th>
                            <th><input data-col="9" type="text" class="form-control form-control-sm"
                                    placeholder="Contractual"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="mt-2 d-flex gap-2">
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">
                        Reload / Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnReload = document.getElementById('btnReload');
            const btnSync = document.getElementById('btnSync');
            const statusEl = document.getElementById('syncStatus');

            const table = $('#usmmTable').DataTable({
                processing: true,
                deferRender: true,
                pageLength: 25,
                orderCellsTop: true,
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: '{{ route('middle_db.usmm.allData') }}'
                    // Single bulk load; filtering done client-side
                },
                columns: [{
                        data: 'company'
                    },
                    {
                        data: 'sap_user_id'
                    },
                    {
                        data: 'full_name'
                    },
                    {
                        data: 'department'
                    },
                    {
                        data: 'last_logon_date',
                        render: function(data, type) {
                            if (type !== 'display' && type !== 'filter') return data;
                            if (!data || data === '00000000') return 'NULL';
                            const mths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug',
                                'Sep', 'Oct', 'Nov', 'Dec'
                            ];
                            let y, m, d;
                            if (/^\d{8}$/.test(data)) {
                                y = data.slice(0, 4);
                                m = data.slice(4, 6);
                                d = data.slice(6, 8);
                            } else if (/^\d{4}-\d{2}-\d{2}/.test(data)) {
                                [y, m, d] = data.split('-');
                            } else return data;
                            return d + '-' + mths[+m - 1] + '-' + y;
                        }
                    },
                    {
                        data: 'last_logon_time',
                        render: function(data, type) {
                            if (type !== 'display' && type !== 'filter') return data;
                            if (!data) return '';
                            if (/^\d{6}$/.test(data)) return data.slice(0, 2) + ':' + data.slice(2,
                                4) + ':' + data.slice(4, 6);
                            if (/^\d{2}:\d{2}:\d{2}/.test(data)) return data.slice(0, 8);
                            return data;
                        }
                    },
                    {
                        data: 'user_type_desc'
                    },
                    {
                        data: 'valid_from',
                        render: function(data, type) {
                            if (type !== 'display' && type !== 'filter') return data;
                            if (!data || data === '00000000') return 'NULL';
                            const mths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug',
                                'Sep', 'Oct', 'Nov', 'Dec'
                            ];
                            let y, m, d;
                            if (/^\d{8}$/.test(data)) {
                                y = data.slice(0, 4);
                                m = data.slice(4, 6);
                                d = data.slice(6, 8);
                            } else if (/^\d{4}-\d{2}-\d{2}/.test(data)) {
                                [y, m, d] = data.split('-');
                            } else return data;
                            return d + '-' + mths[+m - 1] + '-' + y;
                        }
                    },
                    {
                        data: 'valid_to',
                        render: function(data, type) {
                            if (type !== 'display' && type !== 'filter') return data;
                            if (!data || data === '00000000') return 'NULL';
                            const mths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug',
                                'Sep', 'Oct', 'Nov', 'Dec'
                            ];
                            let y, m, d;
                            if (/^\d{8}$/.test(data)) {
                                y = data.slice(0, 4);
                                m = data.slice(4, 6);
                                d = data.slice(6, 8);
                            } else if (/^\d{4}-\d{2}-\d{2}/.test(data)) {
                                [y, m, d] = data.split('-');
                            } else return data;
                            return d + '-' + mths[+m - 1] + '-' + y;
                        }
                    },
                    {
                        data: 'contr_user_type_desc'
                    }
                ],
                initComplete: function() {
                    $('#usmmTable thead tr.filters input').on('keyup change', function() {
                        const colIdx = $(this).data('col');
                        const val = this.value;
                        if (table.column(colIdx).search() !== val) {
                            table.column(colIdx).search(val).draw();
                        }
                    });
                }
            });

            btnReload.addEventListener('click', () => {
                $('#usmmTable thead tr.filters input').each(function() {
                    this.value = '';
                    table.column($(this).data('col')).search('');
                });
                table.ajax.reload(null, false);
            });

            btnSync.addEventListener('click', async () => {
                Swal.fire({
                    title: 'Sync USMM Data?',
                    html: '<div class="text-start small">Operation will:<ul class="mb-0"><li>TRUNCATE local table</li><li>Re-import all data</li></ul></div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, proceed',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33',
                    reverseButtons: true,
                    focusCancel: true
                }).then(async (res) => {
                    if (!res.isConfirmed) return;

                    btnSync.disabled = true;
                    statusEl.textContent = 'Sync in progress...';

                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while syncing data.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        const resp = await fetch('{{ route('middle_db.usmm.sync') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        if (!resp.ok) throw new Error('HTTP ' + resp.status);
                        const data = await resp.json();
                        statusEl.textContent = 'Sync complete. Inserted: ' + (data
                            .inserted ?? '?');
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Sync Complete',
                            text: 'Inserted: ' + (data.inserted ?? '?'),
                            timer: 4000,
                            showConfirmButton: false
                        });
                    } catch (e) {
                        console.error(e);
                        statusEl.textContent = 'Sync failed.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Sync Failed',
                            text: e.message || 'Unknown error'
                        });
                    } finally {
                        btnSync.disabled = false;
                        setTimeout(() => statusEl.textContent = '', 8000);
                    }
                });
            });
        });
    </script>
@endsection
