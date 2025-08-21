@extends('layouts.app')

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
            <div class="card-header">
                <h2 class="mb-0">Middle DB - Master Data Karyawan</h2>
            </div>
            <div class="card-body">

                <div class="d-flex mb-3 gap-2 flex-wrap">
                    <button id="btnSync" class="btn btn-primary btn-sm">Sync Data</button>
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload / Clear Filters</button>
                    <span id="syncStatus" class="text-muted small"></span>
                </div>

                <table id="karyawanTable" class="table table-sm table-striped table-bordered w-100 table-responsive">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Direktorat ID</th>
                            <th>Direktorat</th>
                            <th>Kompartemen ID</th>
                            <th>Kompartemen</th>
                            <th>Departemen ID</th>
                            <th>Departemen</th>
                            <th>Cost Center</th>
                        </tr>
                        <tr class="filters">
                            <th></th>
                            <th><input data-col="1" type="text" class="form-control form-control-sm"
                                    placeholder="Company"></th>
                            <th><input data-col="2" type="text" class="form-control form-control-sm" placeholder="NIK">
                            </th>
                            <th><input data-col="3" type="text" class="form-control form-control-sm"
                                    placeholder="Nama"></th>
                            <th><input data-col="4" type="text" class="form-control form-control-sm"
                                    placeholder="Dir ID"></th>
                            <th><input data-col="5" type="text" class="form-control form-control-sm"
                                    placeholder="Direktorat"></th>
                            <th><input data-col="6" type="text" class="form-control form-control-sm"
                                    placeholder="Komp ID"></th>
                            <th><input data-col="7" type="text" class="form-control form-control-sm"
                                    placeholder="Kompartemen"></th>
                            <th><input data-col="8" type="text" class="form-control form-control-sm"
                                    placeholder="Dept ID"></th>
                            <th><input data-col="9" type="text" class="form-control form-control-sm"
                                    placeholder="Departemen"></th>
                            <th><input data-col="10" type="text" class="form-control form-control-sm"
                                    placeholder="Cost Center"></th>
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
            const btnSync = document.getElementById('btnSync');
            const btnReload = document.getElementById('btnReload');
            const statusEl = document.getElementById('syncStatus');

            const tbl = $('#karyawanTable').DataTable({
                processing: true,
                deferRender: true,
                pageLength: 25,
                orderCellsTop: true,
                ajax: '{{ route('middle_db.master_data_karyawan.data') }}',
                columns: [{
                        data: 'id',
                        visible: false
                    },
                    {
                        data: 'company'
                    },
                    {
                        data: 'nik'
                    },
                    {
                        data: 'nama'
                    },
                    {
                        data: 'direktorat_id'
                    },
                    {
                        data: 'direktorat'
                    },
                    {
                        data: 'kompartemen_id'
                    },
                    {
                        data: 'kompartemen'
                    },
                    {
                        data: 'departemen_id'
                    },
                    {
                        data: 'departemen'
                    },
                    {
                        data: 'cost_center'
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                initComplete: function() {
                    $('#karyawanTable thead tr.filters input').on('keyup change', function() {
                        const colIdx = $(this).data('col');
                        const val = this.value;
                        if (tbl.column(colIdx).search() !== val) {
                            tbl.column(colIdx).search(val).draw();
                        }
                    });
                }
            });

            btnReload.addEventListener('click', () => {
                $('#karyawanTable thead tr.filters input').each(function() {
                    this.value = '';
                    tbl.column($(this).data('col')).search('');
                });
                tbl.ajax.reload(null, false);
            });

            btnSync.addEventListener('click', async () => {
                if (!confirm('Sync will TRUNCATE and reload data. Continue?')) return;
                btnSync.disabled = true;
                statusEl.textContent = 'Sync in progress...';
                try {
                    const resp = await fetch('{{ route('middle_db.master_data_karyawan.sync') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();
                    statusEl.textContent = 'Done. Inserted: ' + (data.inserted ?? '?');
                    tbl.ajax.reload(null, false);
                } catch (e) {
                    console.error(e);
                    statusEl.textContent = 'Error during sync.';
                    alert('Sync failed.');
                } finally {
                    btnSync.disabled = false;
                    setTimeout(() => statusEl.textContent = '', 8000);
                }
            });
        });
    </script>
@endsection
