@extends('layouts.app')

@section('title', 'USMM Master - Expired Users')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h2 class="mb-0 flex-grow-1">USMM - Expired Users (valid_to &lt; today)</h2>
                <div class="d-flex gap-2">
                    <input type="text" id="searchExpired" class="form-control form-control-sm" placeholder="Search..."
                        style="min-width:220px">
                    <button id="btnReloadExpired" class="btn btn-outline-secondary btn-sm">Reload Table</button>
                </div>
            </div>
            <div class="card-body">
                <table id="usmmExpiredTable" class="table table-sm table-striped table-bordered w-100">
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
            const searchInput = document.getElementById('searchExpired');
            const btnReload = document.getElementById('btnReloadExpired');

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

            const table = $('#usmmExpiredTable').DataTable({
                processing: true,
                deferRender: true,
                pageLength: 25,
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: '{{ route('middle_db.usmm.expiredData') }}',
                    dataSrc: function(json) {
                        const q = searchInput.value.trim().toLowerCase();
                        if (!q) return json.data;
                        return json.data.filter(r =>
                            (r.sap_user_id || '').toLowerCase().includes(q) ||
                            (r.full_name || '').toLowerCase().includes(q) ||
                            (r.company || '').toLowerCase().includes(q) ||
                            (r.department || '').toLowerCase().includes(q)
                        );
                    }
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
                ]
            });

            let typingTimer;
            searchInput.addEventListener('keyup', () => {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => table.ajax.reload(), 350);
            });
            searchInput.addEventListener('keydown', () => clearTimeout(typingTimer));

            btnReload.addEventListener('click', () => table.ajax.reload(null, false));
        });
    </script>
@endsection
