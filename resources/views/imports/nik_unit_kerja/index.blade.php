@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h5 mb-0">Import User NIK → Unit Kerja</h1>
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-4">
                        <label for="periode_id" class="form-label">Periode</label>
                        <select id="periode_id" class="form-select">
                            <option value="">-- Pilih Periode --</option>
                            @foreach ($periodes as $p)
                                <option value="{{ $p->id }}">{{ $p->definisi ?? 'Periode ' . $p->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button id="btn-load" class="btn btn-secondary">Tampilkan</button>
                        <button id="btn-reset" class="btn btn-outline-secondary">Reset</button>
                    </div>
                    <div class="col text-end">
                        <button id="btn-import-selected" class="btn btn-primary" disabled>Import Selected</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="grid" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="chk-all" /></th>
                                <th>Company</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Kompartemen ID</th>
                                <th>Kompartemen</th>
                                <th>Departemen ID</th>
                                <th>Departemen</th>
                                <th>Atasan</th>
                                <th>Cost Center</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="mt-2 text-muted small">
                    Hanya NIK yang belum ada pada ms_nik_unit_kerja (untuk Periode terpilih) yang ditampilkan.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let table = null;

        function selectedNiks() {
            const arr = [];
            document.querySelectorAll('.row-chk:checked').forEach(el => arr.push(el.value));
            return arr;
        }

        function toggleImportButton() {
            document.getElementById('btn-import-selected').disabled = selectedNiks().length === 0;
        }

        function initTable() {
            if (table) {
                table.destroy();
                $('#grid').empty(); // clear header/body then rebuild
                $('#grid').append(`
                <thead>
                    <tr>
                        <th><input type="checkbox" id="chk-all" /></th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Company</th>
                        <th>Kompartemen ID</th>
                        <th>Kompartemen</th>
                        <th>Departemen ID</th>
                        <th>Departemen</th>
                        <th>Atasan</th>
                        <th>Cost Center</th>
                    </tr>
                </thead>
            `);
            }

            table = $('#grid').DataTable({
                ajax: function(data, callback) {
                    const pid = $('#periode_id').val();
                    if (!pid) {
                        return callback({
                            data: []
                        });
                    }
                    fetch("{{ route('import.nik_unit_kerja.data') }}?periode_id=" + encodeURIComponent(
                            pid), {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(json => callback(json));
                },
                columns: [{
                        data: 'nik',
                        orderable: false,
                        render: (nik) => `<input type="checkbox" class="row-chk" value="${nik}">`
                    },
                    {
                        data: 'nik'
                    },
                    {
                        data: 'nama',
                        defaultContent: ''
                    },
                    {
                        data: 'company_id',
                        defaultContent: ''
                    },
                    {
                        data: 'kompartemen_id',
                        defaultContent: ''
                    },
                    {
                        data: 'kompartemen',
                        defaultContent: ''
                    },
                    {
                        data: 'departemen_id',
                        defaultContent: ''
                    },
                    {
                        data: 'departemen',
                        defaultContent: ''
                    },
                    {
                        data: 'atasan',
                        defaultContent: ''
                    },
                    {
                        data: 'cost_center',
                        defaultContent: ''
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                deferRender: true,
                pageLength: 25
            });

            // Checkbox handlers
            $('#grid').on('change', '#chk-all', function() {
                const checked = this.checked;
                document.querySelectorAll('.row-chk').forEach(el => {
                    el.checked = checked;
                });
                toggleImportButton();
            });

            $('#grid').on('change', '.row-chk', function() {
                toggleImportButton();
            });
        }

        $(function() {
            initTable();

            $('#btn-load').on('click', () => table.ajax.reload());
            $('#periode_id').on('change', () => table.ajax.reload());
            $('#btn-reset').on('click', () => {
                $('#periode_id').val('');
                table.ajax.reload();
            });

            $('#btn-import-selected').on('click', function() {
                const pid = $('#periode_id').val();
                const niks = selectedNiks();
                if (!pid) {
                    alert('Pilih Periode terlebih dahulu.');
                    return;
                }
                if (niks.length === 0) {
                    alert('Pilih minimal satu NIK.');
                    return;
                }

                if (!confirm(`Import ${niks.length} data?`)) return;

                fetch("{{ route('import.nik_unit_kerja.import') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            periode_id: pid,
                            niks
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.error) {
                            alert('Import gagal: ' + res.error);
                        } else {
                            alert(`Selesai. Ditambahkan: ${res.inserted}`);
                            table.ajax.reload(null, false);
                            document.getElementById('chk-all').checked = false;
                            toggleImportButton();
                        }
                    })
                    .catch(err => alert('Terjadi kesalahan saat import.'));
            });
        });
    </script>
@endsection
