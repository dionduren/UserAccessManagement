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
                <h2 class="mb-0 flex-grow-1">Middle DB - UAM Relationship (RAW)</h2>
                <form id="syncForm" class="d-flex gap-2 flex-wrap">
                    @csrf
                    <input type="text" name="like" id="likeFilter" class="form-control form-control-sm"
                        style="max-width:180px" value="Z%" placeholder="Composite LIKE" hidden>
                    <button type="button" id="btnSync" class="btn btn-primary btn-sm">
                        Sync Data
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="mb-2 small text-muted" id="syncStatus"></div>

                <table id="uamTable" class="table table-sm table-striped table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>SAP User</th>
                            <th>Composite Role</th>
                            <th>Single Role</th>
                            <th>TCode</th>
                            <th>Synced At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="mt-2 d-flex gap-2">
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">
                        Reload Table
                    </button>
                    <button id="btnApplyFilter" class="btn btn-outline-primary btn-sm">
                        Apply Filter (no sync)
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const likeInput = document.getElementById('likeFilter');
            const syncBtn = document.getElementById('btnSync');
            const reloadBtn = document.getElementById('btnReload');
            const applyBtn = document.getElementById('btnApplyFilter');
            const statusEl = document.getElementById('syncStatus');

            const table = $('#uamTable').DataTable({
                processing: true,
                deferRender: true,
                pageLength: 25,
                order: [
                    [1, 'asc'],
                    // [2, 'asc'],
                    // [3, 'asc'],
                    // [4, 'asc']
                ],
                ajax: {
                    url: '{{ route('middle_db.raw.uam_relationship.data') }}',
                    data: function(d) {
                        d.like = likeInput.value; // optional if controller supports filtering
                    }
                },
                columns: [{
                        data: 'id',
                        visible: false
                    },
                    {
                        data: 'sap_user'
                    },
                    {
                        data: 'composite_role'
                    },
                    {
                        data: 'single_role'
                    },
                    {
                        data: 'tcode'
                    },
                    {
                        data: 'created_at',
                        render: function(val) {
                            return val ? new Date(val).toLocaleString() : '';
                        }
                    }
                ]
            });

            reloadBtn.addEventListener('click', () => {
                table.ajax.reload(null, false);
            });

            applyBtn.addEventListener('click', () => {
                table.ajax.reload();
            });

            syncBtn.addEventListener('click', async () => {
                if (!confirm('This will TRUNCATE and re-import data. Continue?')) return;
                syncBtn.disabled = true;
                statusEl.textContent = 'Sync in progress...';
                try {
                    const formData = new FormData();
                    formData.append('like', likeInput.value);
                    const resp = await fetch('{{ route('middle_db.raw.uam_relationship.sync') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();
                    statusEl.textContent = `Sync complete. Inserted: ${data.inserted}`;
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
