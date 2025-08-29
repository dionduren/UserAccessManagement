@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0 flex-grow-1">Duplicate Master Data Karyawan (Per Nama)</h5>
                <div class="d-flex gap-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="chkPerCompany">
                        <label for="chkPerCompany" class="form-check-label small">Per Company</label>
                    </div>
                    <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
                    <button id="btnAddSelected" class="btn btn-primary btn-sm">Add Selected</button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Menampilkan semua baris dengan nama yang muncul lebih dari satu kali.
                    Centang baris yang ingin dimasukkan ke DuplicateNameFilter lalu klik "Add Selected".
                    Opsi "Per Company" menghitung duplikasi berdasarkan (company,nama).
                </p>
                <table id="dupTable" class="table table-sm table-bordered table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30px">
                                <input type="checkbox" id="chkAll">
                            </th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Company</th>
                            <th>Kompartemen</th>
                            <th>Departemen</th>
                            <th style="width:70px">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div id="dupStatus" class="small text-muted mt-2"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const $table = $('#dupTable');
            let dt = initTable();

            function initTable() {
                return $table.DataTable({
                    ajax: {
                        url: '{{ route('middle_db.master_data_karyawan.duplicates.data') }}',
                        data: function(d) {
                            d.per_company = document.getElementById('chkPerCompany').checked ? 1 : 0;
                        }
                    },
                    deferRender: true,
                    pageLength: 25,
                    lengthMenu: [
                        [25, 50, 100],
                        [25, 50, 100]
                    ],
                    order: [
                        [2, 'asc'],
                        [3, 'asc'],
                        [1, 'asc']
                    ], // by nama, company, nik
                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(d, t, row) {
                                const disabled = row.in_filter ? 'disabled' : '';
                                return `<input type="checkbox" class="row-check" value="${row.nik}" ${disabled}>`;
                            }
                        },
                        {
                            data: 'nik'
                        },
                        {
                            data: 'nama'
                        },
                        {
                            data: 'company'
                        },
                        {
                            data: 'kompartemen'
                        },
                        {
                            data: 'departemen'
                        },
                        {
                            data: 'in_filter',
                            className: 'text-center',
                            render: function(val) {
                                return val ?
                                    '<span class="badge bg-success">In Filter</span>' :
                                    '<span class="badge bg-light text-dark">New</span>';
                            }
                        }
                    ]
                });
            }

            // Reload
            document.getElementById('btnReload').addEventListener('click', function() {
                dt.ajax.reload(null, false);
            });

            // Toggle per company triggers rebuild (so ordering resets)
            document.getElementById('chkPerCompany').addEventListener('change', function() {
                dt.ajax.reload(null, true);
            });

            // Select / Deselect all
            document.getElementById('chkAll').addEventListener('change', function() {
                const checked = this.checked;
                $table.find('tbody input.row-check:not(:disabled)').prop('checked', checked);
            });

            // If any row unchecked -> uncheck header
            $table.on('change', 'tbody input.row-check', function() {
                if (!this.checked) {
                    document.getElementById('chkAll').checked = false;
                }
            });

            // Add Selected
            document.getElementById('btnAddSelected').addEventListener('click', async function() {
                const nikSelected = $table.find('tbody input.row-check:checked').map(function() {
                    return this.value;
                }).get();

                if (!nikSelected.length) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak ada pilihan',
                        text: 'Pilih minimal satu baris.'
                    });
                    return;
                }

                const confirmRes = await Swal.fire({
                    title: 'Tambah ke DuplicateNameFilter?',
                    html: `Jumlah dipilih: <b>${nikSelected.length}</b>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal'
                });
                if (!confirmRes.isConfirmed) return;

                try {
                    const resp = await fetch(
                        '{{ route('middle_db.master_data_karyawan.duplicates.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                niks: nikSelected
                            })
                        });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();

                    Swal.fire({
                        icon: 'success',
                        title: 'Selesai',
                        html: `Inserted: <b>${data.inserted}</b><br>Existing: <b>${data.existing}</b>`,
                        timer: 4000,
                        showConfirmButton: false
                    });

                    document.getElementById('chkAll').checked = false;
                    dt.ajax.reload(null, false);
                } catch (e) {
                    console.error(e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat menyimpan data.'
                    });
                }
            });

        });
    </script>
@endsection
