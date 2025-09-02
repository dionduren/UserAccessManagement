@extends('layouts.app')

@section('title', 'USMM Master - Inactive > 6 Months')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h2 class="mb-0 flex-grow-1">USMM - Active Generic Users</h2>
                <div class="d-flex gap-2">
                    <button id="btnReloadInactive" class="btn btn-outline-secondary btn-sm">Reload / Clear Filters</button>
                </div>
            </div>
            <div class="card-body">
                <table id="usmmInactiveTable" class="table table-sm table-striped table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>SAP User ID</th>
                            <th>Full Name</th>
                            <th>Department</th>
                            <th width="7.5%">Last Logon Date</th>
                            <th width="7.5%">Last Logon Time</th>
                            <th>User Type</th>
                            <th width="7.5%">Valid From</th>
                            <th width="7.5%">Valid To</th>
                            <th>Contractual Type</th>
                            <th>Creator</th>
                            <th>Created On</th>
                            <th>Synced At</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" type="text" class="form-control form-control-sm"
                                    placeholder="Company">
                            </th>
                            <th><input data-col="1" type="text" class="form-control form-control-sm"
                                    placeholder="User ID">
                            </th>
                            <th><input data-col="2" type="text" class="form-control form-control-sm"
                                    placeholder="Full Name">
                            </th>
                            <th><input data-col="3" type="text" class="form-control form-control-sm"
                                    placeholder="Department">
                            </th>
                            <th><input data-col="4" type="text" class="form-control form-control-sm"
                                    placeholder="Logon Date">
                            </th>
                            <th><input data-col="5" type="text" class="form-control form-control-sm"
                                    placeholder="Logon Time">
                            </th>
                            <th><input data-col="6" type="text" class="form-control form-control-sm"
                                    placeholder="User Type">
                            </th>
                            <th><input data-col="7" type="text" class="form-control form-control-sm"
                                    placeholder="Valid From">
                            </th>
                            <th><input data-col="8" type="text" class="form-control form-control-sm"
                                    placeholder="Valid To">
                            </th>
                            <th><input data-col="9" type="text" class="form-control form-control-sm"
                                    placeholder="Contractual">
                            </th>
                            <th><input data-col="10" type="text" class="form-control form-control-sm"
                                    placeholder="Creator">
                            </th>
                            <th><input data-col="10" type="text" class="form-control form-control-sm"
                                    placeholder="Created On">
                            </th>
                            <th><input data-col="10" type="text" class="form-control form-control-sm"
                                    placeholder="Synced At">
                            </th>
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
        document.addEventListener('DOMContentLoaded', function() {
            const btnReload = document.getElementById('btnReloadInactive');

            const formatDate = (data) => {
                if (!data || data === '00000000') return 'NULL';
                const mths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                    'Dec'
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
            };

            const formatTime = (data) => {
                if (!data) return '';
                if (/^\d{6}$/.test(data)) return data.slice(0, 2) + ':' + data.slice(2, 4) + ':' + data.slice(4,
                    6);
                if (/^\d{2}:\d{2}:\d{2}/.test(data)) return data.slice(0, 8);
                return data;
            };

            const table = $('#usmmInactiveTable').DataTable({
                processing: true,
                deferRender: true,
                pageLength: 25,
                orderCellsTop: true,
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: '{{ route('middle_db.usmm.activeGenericData') }}'
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
                        render: (d, t) => (t === 'display' || t === 'filter') ? formatDate(d) : d
                    },
                    {
                        data: 'last_logon_time',
                        render: (d, t) => (t === 'display' || t === 'filter') ? formatTime(d) : d
                    },
                    {
                        data: 'user_type_desc'
                    },
                    {
                        data: 'valid_from',
                        render: (d, t) => (t === 'display' || t === 'filter') ? formatDate(d) : d
                    },
                    {
                        data: 'valid_to',
                        render: (d, t) => (t === 'display' || t === 'filter') ? formatDate(d) : d
                    },
                    {
                        data: 'contr_user_type_desc'
                    },
                    {
                        data: 'creator'
                    },
                    {
                        data: 'creator_created_at',
                        render: (d, t) => (t === 'display' || t === 'filter') ? formatDate(d) : d
                    },
                    {
                        data: 'created_at',
                        render: function(val) {
                            return val ? new Date(val).toLocaleString('en-GB') : '';
                        }
                    }
                ],
                initComplete: function() {
                    $('#usmmInactiveTable thead tr.filters input').on('keyup change', function() {
                        const colIdx = $(this).data('col');
                        const val = this.value;
                        if (table.column(colIdx).search() !== val) {
                            table.column(colIdx).search(val).draw();
                        }
                    });
                }
            });

            btnReload.addEventListener('click', () => {
                $('#usmmInactiveTable thead tr.filters input').each(function() {
                    this.value = '';
                    table.column($(this).data('col')).search('');
                });
                table.ajax.reload(null, false);
            });
        });
    </script>
@endsection
