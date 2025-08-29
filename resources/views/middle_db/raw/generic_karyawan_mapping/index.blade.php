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
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h2 class="mb-0 flex-grow-1">Middle DB - Generic Karyawan Mapping (RAW)</h2>
                <button type="button" id="btnSync" class="btn btn-primary btn-sm">
                    Sync Data
                </button>
            </div>
            <div class="card-body">
                <div id="syncStatus" class="small text-muted mb-2"></div>

                <table id="mappingTable" class="table table-sm table-striped table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>SAP User ID</th>
                            <th>User Full Name</th>
                            <th>Company</th>
                            <th>Personnel No</th>
                            <th>Employee Full Name</th>
                            <th>Synced At</th>
                        </tr>
                        <tr class="filters">
                            <th></th>
                            <th><input data-col="1" type="text" class="form-control form-control-sm"
                                    placeholder="SAP User ID"></th>
                            <th><input data-col="2" type="text" class="form-control form-control-sm"
                                    placeholder="User Full Name"></th>
                            <th><input data-col="3" type="text" class="form-control form-control-sm"
                                    placeholder="Company"></th>
                            <th><input data-col="4" type="text" class="form-control form-control-sm"
                                    placeholder="Personnel No"></th>
                            <th><input data-col="5" type="text" class="form-control form-control-sm"
                                    placeholder="Employee Full Name"></th>
                            <th>
                                {{-- <input data-col="6" type="text" class="form-control form-control-sm"
                                    placeholder="Synced At"> --}}
                            </th>
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
            const syncBtn = document.getElementById('btnSync');
            const reloadBtn = document.getElementById('btnReload');
            const statusEl = document.getElementById('syncStatus');

            const table = $('#mappingTable').DataTable({
                processing: true,
                deferRender: true,
                pageLength: 25,
                orderCellsTop: true,
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: '{{ route('middle_db.raw.generic_karyawan_mapping.data') }}'
                },
                columns: [{
                        data: 'id',
                        visible: false
                    },
                    {
                        data: 'sap_user_id'
                    },
                    {
                        data: 'user_full_name'
                    },
                    {
                        data: 'company'
                    },
                    {
                        data: 'personnel_number'
                    },
                    {
                        data: 'employee_full_name'
                    },
                    {
                        data: 'created_at',
                        render: v => v ? new Date(v).toLocaleString() : ''
                    }
                ],
                initComplete: function() {
                    $('#mappingTable thead tr.filters input').on('keyup change', function() {
                        const colIdx = $(this).data('col');
                        const val = this.value;
                        if (table.column(colIdx).search() !== val) {
                            table.column(colIdx).search(val).draw();
                        }
                    });
                }
            });

            reloadBtn.addEventListener('click', () => {
                $('#mappingTable thead tr.filters input').each(function() {
                    this.value = '';
                    table.column($(this).data('col')).search('');
                });
                table.ajax.reload(null, false);
            });

            syncBtn.addEventListener('click', async () => {
                const confirmResult = await Swal.fire({
                    title: 'Konfirmasi Sync',
                    html: 'Proses ini akan <b>TRUNCATE</b> dan memuat ulang data.<br>Lanjutkan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                });
                if (!confirmResult.isConfirmed) return;

                syncBtn.disabled = true;
                statusEl.textContent = 'Sync in progress...';

                const loading = Swal.fire({
                    title: 'Sedang Sinkronisasi',
                    text: 'Mohon tunggu...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const resp = await fetch(
                        '{{ route('middle_db.raw.generic_karyawan_mapping.sync') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        html: 'Sync selesai.<br>Inserted: <b>' + data.inserted + '</b>',
                        timer: 4000,
                        showConfirmButton: false
                    });
                    statusEl.textContent = 'Sync complete. Inserted: ' + data.inserted;
                    table.ajax.reload(null, false);
                } catch (e) {
                    console.error(e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Sync gagal dijalankan.'
                    });
                    statusEl.textContent = 'Sync failed.';
                } finally {
                    syncBtn.disabled = false;
                    setTimeout(() => statusEl.textContent = '', 8000);
                }
            });
        });
    </script>
@endsection
