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
                    <div class="col text-end">
                        <button id="btn-import-selected" class="btn btn-primary" disabled>Import Selected</button>
                        <button id="btn-import-all" class="btn btn-outline-primary" disabled>Import All</button>
                        <button id="btn-reset" class="btn btn-outline-secondary">Reset</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let table = null;

        function toast(icon, title) {
            Swal.fire({
                icon,
                title,
                toast: true,
                position: 'top-end',
                timer: 2500,
                showConfirmButton: false
            });
        }

        function selectedNiks() {
            const arr = [];
            document.querySelectorAll('.row-chk:checked').forEach(el => arr.push(el.value));
            return arr;
        }

        function toggleImportButtons() {
            document.getElementById('btn-import-selected').disabled = selectedNiks().length === 0;
            const pid = $('#periode_id').val();
            document.getElementById('btn-import-all').disabled = !pid;
        }

        function initTable() {
            if (table) {
                table.destroy();
                $('#grid').empty().append(`
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
                `);
            }

            table = $('#grid').DataTable({
                ajax: (data, callback) => {
                    const pid = $('#periode_id').val();
                    if (!pid) return callback({
                        data: []
                    });
                    fetch("{{ route('import.nik_unit_kerja.data') }}?periode_id=" + encodeURIComponent(pid), {
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
                        render: nik => `<input type="checkbox" class="row-chk" value="${nik}">`
                    },
                    {
                        data: 'company_id',
                        defaultContent: ''
                    },
                    {
                        data: 'nik'
                    },
                    {
                        data: 'nama',
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
                    },
                ],
                order: [
                    [2, 'asc']
                ],
                pageLength: 25,
                deferRender: true
            });

            $('#grid').on('change', '#chk-all', function() {
                const checked = this.checked;
                document.querySelectorAll('.row-chk').forEach(el => el.checked = checked);
                toggleImportButtons();
            });
            $('#grid').on('change', '.row-chk', toggleImportButtons);
        }

        $(function() {
            initTable();

            $('#periode_id').on('change', () => {
                table.ajax.reload();
                document.getElementById('chk-all').checked = false;
                toggleImportButtons();
            });

            $('#btn-reset').on('click', () => {
                $('#periode_id').val('');
                table.ajax.reload();
                document.getElementById('chk-all').checked = false;
                toggleImportButtons();
                toast('info', 'Reset selesai');
            });

            $('#btn-import-selected').on('click', function() {
                const pid = $('#periode_id').val();
                const niks = selectedNiks();
                if (!pid) {
                    toast('warning', 'Pilih periode');
                    return;
                }
                if (niks.length === 0) {
                    toast('warning', 'Tidak ada NIK');
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi',
                    text: `Import ${niks.length} NIK terpilih?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, import'
                }).then(res => {
                    if (!res.isConfirmed) return;
                    Swal.showLoading();

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
                                Swal.fire('Gagal', res.error, 'error');
                            } else {
                                Swal.fire('Sukses', `Ditambahkan: ${res.inserted}`, 'success');
                                table.ajax.reload(null, false);
                                document.getElementById('chk-all').checked = false;
                                toggleImportButtons();
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Terjadi kesalahan', 'error'));
                });
            });

            $('#btn-import-all').on('click', function() {
                const pid = $('#periode_id').val();
                if (!pid) {
                    toast('warning', 'Pilih periode');
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Import semua NIK baru periode ini (dengan data lengkap)?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, import semua'
                }).then(res => {
                    if (!res.isConfirmed) return;
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch("{{ route('import.nik_unit_kerja.import_all') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({
                                periode_id: pid
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.error) {
                                Swal.fire('Gagal', res.error, 'error');
                            } else {
                                Swal.fire('Sukses', `Ditambahkan: ${res.inserted}`, 'success');
                                table.ajax.reload(null, false);
                                document.getElementById('chk-all').checked = false;
                                toggleImportButtons();
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Terjadi kesalahan', 'error'));
                });
            });

            toggleImportButtons();
        });
    </script>
@endsection
