@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0 flex-grow-1">Generic Karyawan Mapping (Filtered by DuplicateNameFilter)</h5>
                <button id="btnSync" class="btn btn-outline-primary btn-sm">Sync</button>
                <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Menampilkan mapping yang sudah menerapkan filter duplikasi nama:<br>
                    1) Jika nama tidak terdapat di DuplicateNameFilter maka tetap tampil.<br>
                    2) Jika nama terdapat di DuplicateNameFilter, hanya baris dengan personnel_number
                    yang terdaftar sebagai nik di DuplicateNameFilter (nama yang sama) yang ditampilkan.<br>
                    Badge Duplicate menunjukkan nama tersebut masuk daftar duplikat; Allowed berarti baris lolos filter.
                </p>
                <table id="gkmFilteredTable" class="table table-sm table-bordered table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">#</th>
                            <th>User Full Name</th>
                            <th>Personnel No</th>
                            <th>SAP User ID</th>
                            <th>Employee Full Name</th>
                            <th>Company</th>
                            <th style="width:120px">Status</th>
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
            const tbl = $('#gkmFilteredTable').DataTable({
                ajax: '{{ route('middle_db.generic_karyawan_mapping.data') }}',
                pageLength: 50,
                lengthMenu: [
                    [25, 50, 100, 200],
                    [25, 50, 100, 200]
                ],
                order: [
                    [1, 'asc'],
                    [2, 'asc']
                ],
                deferRender: true,
                columns: [{
                        data: null,
                        className: 'text-end',
                        render: (d, t, r, m) => m.row + 1
                    },
                    {
                        data: 'user_full_name'
                    },
                    {
                        data: 'personnel_number'
                    },
                    {
                        data: 'sap_user_id'
                    },
                    {
                        data: 'employee_full_name'
                    },
                    {
                        data: 'company'
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: function(row) {
                            let badges = [];
                            if (row.duplicate_name) {
                                badges.push(
                                    '<span class="badge bg-warning text-dark">Duplicate</span>');
                            }
                            if (row.filtered_in) {
                                badges.push('<span class="badge bg-success">Allowed</span>');
                            } else if (row.duplicate_name) {
                                badges.push('<span class="badge bg-danger">Filtered Out</span>');
                            }
                            return badges.join('<br>');
                        }
                    }
                ]
            });

            document.getElementById('btnReload').addEventListener('click', () => tbl.ajax.reload(null, false));

            document.getElementById('btnSync').addEventListener('click', async () => {
                if (!confirm('Sync ulang data raw mapping?')) return;
                try {
                    const resp = await fetch(
                        '{{ route('middle_db.generic_karyawan_mapping.sync') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();
                    alert('Sync selesai. Inserted: ' + data.inserted);
                    tbl.ajax.reload(null, true);
                } catch (e) {
                    console.error(e);
                    alert('Sync gagal.');
                }
            });
        });
    </script>
@endsection
