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
                <h2>Middle DB - Unit Kerja</h2>
            </div>
            <div class="card-body">

                <div class="d-flex mb-3 gap-2">
                    <button id="btnSync" class="btn btn-primary btn-sm">
                        Sync Data
                    </button>
                    <span id="syncStatus" class="text-muted small"></span>
                </div>

                <table id="karyawanTable" class="table table-sm table-striped table-bordered w-100 table-responsive">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Direktorat ID</th>
                            <th>Direktorat</th>
                            <th>Kompartemen ID</th>
                            <th>Kompartemen</th>
                            <th>Departemen ID</th>
                            <th>Departemen</th>
                            <th>Cost Center</th>
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
            const tbl = $('#karyawanTable').DataTable({
                processing: true,
                ajax: '{{ route('middle_db.unit_kerja.data') }}',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'company'
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
                columnDefs: [{
                    targets: 0,
                    visible: false
                }]
            });

            const btn = document.getElementById('btnSync');
            const statusEl = document.getElementById('syncStatus');

            btn.addEventListener('click', async () => {
                if (!confirm('Sync will TRUNCATE and reload data. Continue?')) return;
                btn.disabled = true;
                statusEl.textContent = 'Sync in progress...';
                try {
                    const resp = await fetch('{{ route('middle_db.unit_kerja.sync') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();
                    statusEl.textContent = `Done. Inserted: ${data.inserted}`;
                    tbl.ajax.reload(null, false);
                } catch (e) {
                    console.error(e);
                    statusEl.textContent = 'Error during sync.';
                    alert('Sync failed.');
                } finally {
                    btn.disabled = false;
                    setTimeout(() => statusEl.textContent = '', 8000);
                }
            });
        });
    </script>
@endsection
