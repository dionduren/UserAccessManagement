@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h2 class="mb-0 flex-grow-1">Single Roles</h2>
                <div class="d-flex gap-2">
                    <div hidden>
                        <input id="like1" class="form-control form-control-sm" style="width:140px" value="ZS-%"
                            title="LIKE #1">
                        <input id="like2" class="form-control form-control-sm" style="width:140px" value="%-AO%"
                            title="LIKE #2">
                    </div>
                    <a href="{{ route('compare.uam.single.exist') }}" target="_blank" class="btn btn-success btn-sm">Compare
                        Existing Data</a>
                    <a href="{{ route('compare.uam.single') }}" target="_blank" class="btn btn-warning btn-sm">Compare
                        Empty Data</a>
                    <button id="btnSync" class="btn btn-primary btn-sm">Sync</button>
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
                </div>
            </div>
            <div class="card-body">
                <div id="syncStatus" class="small text-muted mb-2"></div>
                <table id="mainTable" class="table table-sm table-striped table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Single Role</th>
                            <th>Description</th>
                            <th>Synced At</th>
                        </tr>
                        <tr class="filters">
                            <th></th>
                            <th><input data-col="1" type="text" class="form-control form-control-sm"
                                    placeholder="Role"></th>
                            <th><input data-col="2" type="text" class="form-control form-control-sm"
                                    placeholder="Desc"></th>
                            <th><input data-col="3" type="text" class="form-control form-control-sm"
                                    placeholder="Created"></th>
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
            const table = $('#mainTable').DataTable({
                processing: true,
                deferRender: true,
                pageLength: 25,
                orderCellsTop: true,
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: '{{ route('middle_db.uam.single_role.data') }}'
                },
                columns: [{
                        data: 'id',
                        visible: false
                    },
                    {
                        data: 'single_role'
                    },
                    {
                        data: 'definisi'
                    },
                    {
                        data: 'created_at',
                        render: v => v ? new Date(v).toLocaleString() : ''
                    }
                ],
                initComplete: function() {
                    $('#mainTable thead tr.filters input').on('keyup change', function() {
                        const i = $(this).data('col'),
                            val = this.value;
                        if (table.column(i).search() !== val) {
                            table.column(i).search(val).draw();
                        }
                    });
                }
            });

            const syncBtn = document.getElementById('btnSync');
            const reloadBtn = document.getElementById('btnReload');
            const statusEl = document.getElementById('syncStatus');

            reloadBtn.addEventListener('click', () => {
                $('#mainTable thead tr.filters input').each(function() {
                    this.value = '';
                    table.column($(this).data('col')).search('');
                });
                table.ajax.reload(null, false);
            });

            syncBtn.addEventListener('click', async () => {
                const confirmResult = await Swal.fire({
                    title: 'Confirm Sync',
                    html: 'This will <b>TRUNCATE</b> and reload data.<br>Continue?',
                    icon: 'warning',
                    showCancelButton: true
                });
                if (!confirmResult.isConfirmed) return;

                syncBtn.disabled = true;
                statusEl.textContent = 'Sync in progress...';
                Swal.fire({
                    title: 'Syncing',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const resp = await fetch('{{ route('middle_db.uam.single_role.sync') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            like1: document.getElementById('like1').value || 'ZS-%',
                            like2: document.getElementById('like2').value || '%-AO%'
                        })
                    });
                    if (!resp.ok) throw new Error(resp.status);
                    const data = await resp.json();
                    Swal.fire({
                        icon: 'success',
                        title: 'Done',
                        html: 'Inserted: <b>' + data.inserted + '</b>',
                        timer: 3500,
                        showConfirmButton: false
                    });
                    statusEl.textContent = 'Sync complete. Inserted: ' + data.inserted;
                    table.ajax.reload(null, false);
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Sync failed.'
                    });
                    statusEl.textContent = 'Sync failed.';
                } finally {
                    syncBtn.disabled = false;
                    setTimeout(() => statusEl.textContent = '', 7000);
                }
            });
        });
    </script>
@endsection
