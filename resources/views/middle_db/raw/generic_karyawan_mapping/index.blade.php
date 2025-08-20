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
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="mt-2 d-flex gap-2">
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">
                        Reload Table
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
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: '{{ route('middle_db.raw.generic_karyawan_mapping.data') }}',
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
                ]
            });

            reloadBtn.addEventListener('click', () => {
                table.ajax.reload(null, false);
            });

            syncBtn.addEventListener('click', async () => {
                if (!confirm('Sync will TRUNCATE and reload data. Continue?')) return;
                syncBtn.disabled = true;
                statusEl.textContent = 'Sync in progress...';
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
                    statusEl.textContent = 'Sync complete. Inserted: ' + data.inserted;
                    table.ajax.reload(null, false);
                } catch (e) {
                    console.error(e);
                    statusEl.textContent = 'Sync failed.';
                    alert('Sync failed.');
                } finally {
                    syncBtn.disabled = false;
                    setTimeout(() => statusEl.textContent = '', 8000);
                }
            });
        });
    </script>
@endsection
