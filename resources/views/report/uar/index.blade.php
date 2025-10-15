@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="container-fluid">
        <h3>User Access Review Report</h3>
        <div class="row mb-3">
            <div class="col">
                <label>Periode</label>
                <select name="periode_id" id="periode_id" class="form-control">
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periodes as $p)
                        <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                            {{ $p->definisi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label>Company</label>
                <select name="company" id="company" class="form-control">
                    <option value="">-- Select Company --</option>
                    @foreach ($companies as $comp)
                        <option value="{{ $comp->company_code }}">{{ $comp->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label>Kompartemen</label>
                <select name="kompartemen" id="kompartemen" class="form-control">
                    <option value="">-- Pilih Kompartemen --</option>
                </select>
            </div>
            <div class="col">
                <label>Departemen</label>
                <select name="departemen" id="departemen" class="form-control">
                    <option value="">-- Pilih Departemen --</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-auto">
                <button id="load" class="btn btn-primary mb-3">Load Data</button>
            </div>
            <div class="col-auto">
                <button id="export-word" class="btn btn-success mb-3" style="display: none">Export to Word</button>
            </div>
            <div class="col-auto align-self-center">
                <div id="load-spinner" class="spinner-border text-success" role="status" style="display:none;">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>

        <div class="row mb-3" id="review-table-container" style="display:none;">
            <div class="col-md-2"></div>

            <div class="col-md-8">
                <table class="table table-bordered" style="width: 100%">
                    <tr class="text-center">
                        <th colspan="2" style="background:#bcd;">DOKUMEN REVIEW USER ID DAN OTORISASI</th>
                    </tr>
                    <tr>
                        <td width="30%">Nomor Surat</td>
                        <td id="nomor-surat-cell"></td>
                    </tr>
                    <tr>
                        <td width="30%">Aset Informasi</td>
                        <td>User ID SAP</td>
                    </tr>
                    <tr>
                        <td>Unit Kerja</td>
                        <td id="unit-kerja-cell">-</td>
                    </tr>
                    <tr>
                        <td>Cost Center</td>
                        <td id="cost-center-cell">-</td>
                    </tr>
                    <tr>
                        <td>Jumlah Awal User</td>
                        <td id="jumlah-awal-user-cell"><strong>0</strong></td>
                    </tr>
                    <tr>
                        <td>Jumlah User Berubah</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jumlah User yang Belum Terdata (Baru)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jumlah Akhir User</td>
                        <td></td>
                    </tr>
                </table>
            </div>

            <div class="col-md-2"></div>
        </div>

        <div id="job-role-table-container" class="mt-4" style="display:none;">
            <h5 class="mb-2">Summary User Access Review</h5>
            <br>
            <table id="job-role-table" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>User ID</th>
                        <th width="20%">Nama</th>
                        <th width="20%">Job Role</th>
                        <th width="7.5%">NIK</th>
                        <th style="background-color: #007bff; color: white;">Assigned UID-JobRole</th>
                        <th style="background-color: #007bff; color: white;">Unit Kerja of UID-JobRole</th>
                        <th style="background-color: yellowgreen">User ID (Middle DB)</th>
                        <th style="background-color: yellowgreen">Nama (Middle DB)</th>
                        <th style="background-color: orange">NIK (Master Karyawan)</th>
                    </tr>
                    <tr class="filters">
                        <th></th>
                        <th><input data-col="1" type="text" class="form-control form-control-sm"
                                placeholder="Cari User ID"></th>
                        <th><input data-col="2" type="text" class="form-control form-control-sm"
                                placeholder="Cari Nama"></th>
                        <th><input data-col="3" type="text" class="form-control form-control-sm"
                                placeholder="Cari Job Role"></th>
                        <th><input data-col="4" type="text" class="form-control form-control-sm"
                                placeholder="Cari NIK"></th>
                        <th><input data-col="5" type="text" class="form-control form-control-sm"
                                placeholder="Cari Assigned JobRole"></th>
                        <th><input data-col="6" type="text" class="form-control form-control-sm"
                                placeholder="Cari Unit Kerja"></th>
                        <th><input data-col="7" type="text" class="form-control form-control-sm"
                                placeholder="Cari UID (MDB)"></th>
                        <th><input data-col="8" type="text" class="form-control form-control-sm"
                                placeholder="Cari Nama (MDB)"></th>
                        <th><input data-col="9" type="text" class="form-control form-control-sm"
                                placeholder="Cari NIK (MK)"></th>
                    </tr>
                </thead>
            </table>
        </div>

        <div id="user-system-summary-container" class="mt-4" style="display:none;">
            <h5 class="mb-2">Summary User System</h5>
            <br>
            <div class="table-responsive">
                <table id="user-system-summary-table" class="table table-striped table-bordered" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%;">User ID</th>
                            <th style="width:40%;">Deskripsi</th>
                            <th style="width:30%;">Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- dynamic rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('header-scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {

            // Cascading dropdowns (tidak berubah)
            $('#company').on('change', function() {
                let companyId = $(this).val();
                $('#kompartemen').html('<option value="">-- Pilih Kompartemen --</option>');
                $('#departemen').html('<option value="">-- Pilih Departemen --</option>');
                if (companyId) {
                    $.get("{{ route('report.uar.get-kompartemen') }}", {
                        company_id: companyId
                    }, function(data) {
                        $.each(data, function(i, k) {
                            $('#kompartemen').append(
                                `<option value="${k.kompartemen_id}">${k.nama}</option>`
                            );
                        });
                    });
                    $.get("{{ route('report.uar.get-departemen') }}", {
                        company_id: companyId
                    }, function(data) {
                        $.each(data, function(i, d) {
                            $('#departemen').append(
                                `<option value="${d.departemen_id}">${d.nama}</option>`);
                        });
                    });
                }
            });

            $('#kompartemen').on('change', function() {
                let companyId = $('#company').val();
                let kompartemenId = $(this).val();
                $('#departemen').html('<option value="">-- Pilih Departemen --</option>');
                if (kompartemenId) {
                    $.get("{{ route('report.uar.get-departemen') }}", {
                        company_id: companyId,
                        kompartemen_id: kompartemenId
                    }, function(data) {
                        $.each(data, function(i, d) {
                            $('#departemen').append(
                                `<option value="${d.departemen_id}">${d.nama}</option>`);
                        });
                    });
                } else if (companyId) {
                    $.get("{{ route('report.uar.get-departemen') }}", {
                        company_id: companyId
                    }, function(data) {
                        $.each(data, function(i, d) {
                            $('#departemen').append(
                                `<option value="${d.departemen_id}">${d.nama}</option>`);
                        });
                    });
                }
            });

            let jobRoleTable = $('#job-role-table').DataTable({
                processing: true,
                serverSide: false,
                data: [],
                columns: [{
                        data: null,
                        render: (d, t, r, m) => m.row + 1
                    },
                    {
                        data: 'user_nik'
                    },
                    {
                        data: 'user_definisi'
                    },
                    {
                        data: 'job_role'
                    },
                    {
                        data: 'karyawan_nik'
                    },
                    {
                        data: 'assigned_job_role'
                    },
                    {
                        data: 'assigned_unit_kerja'
                    },
                    {
                        data: 'mdb_usmm.sap_user_id'
                    },
                    {
                        data: 'mdb_usmm.nama'
                    },
                    {
                        data: 'mdb_usmm.nik'
                    },
                ],
                pageLength: 15,
                order: [
                    [1, 'asc']
                ],
                // Keep your paging/search layout
                layout: {
                    top1Start: {
                        div: {
                            className: 'pageLength'
                        }
                    },
                    top1End: {
                        div: {
                            className: 'search'
                        }
                    },
                    bottom1Start: {
                        div: {
                            className: 'info'
                        }
                    },
                    bottom1End: {
                        div: {
                            className: 'paging'
                        }
                    }
                },
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'User_Access_Review',
                    filename: 'UAR_' + new Date().toISOString().slice(0, 10),
                    exportOptions: {
                        // exclude "No" column
                        columns: ':visible:not(:first-child)'
                    }
                }],
                initComplete: function() {
                    const api = this.api();
                    const wrapper = $(api.table().container());

                    // Move built-in controls into custom layout areas
                    wrapper.find('.dt-length').appendTo(wrapper.find('.pageLength').first());
                    wrapper.find('.dt-search').appendTo(wrapper.find('.search').first());
                    wrapper.find('.dt-info').appendTo(wrapper.find('.info').first());
                    wrapper.find('.dt-paging').appendTo(wrapper.find('.paging').first());

                    // Place the Excel button next to the search box
                    api.buttons().container().appendTo(wrapper.find('.search').first());

                    // Wire column filters
                    $('#job-role-table thead tr.filters input').on('keyup change', function() {
                        const colIdx = $(this).data('col');
                        const val = this.value;
                        if (api.column(colIdx).search() !== val) {
                            api.column(colIdx).search(val).draw();
                        }
                    });
                }
            });

            $('#load').on('click', function() {
                let periodeId = $('#periode_id').val();
                if (!periodeId) {
                    Swal.fire('Periode belum dipilih', 'Silakan pilih periode terlebih dahulu', 'warning');
                    return;
                }
                $('#review-table-container, #user-system-summary-container, #job-role-table-container, #export-word')
                    .show();

                let companyId = $('#company').val();
                let kompartemenId = $('#kompartemen').val();
                let departemenId = $('#departemen').val();

                let unitKerja = '-';
                if (departemenId) unitKerja = $('#departemen option:selected').text();
                else if (kompartemenId) unitKerja = $('#kompartemen option:selected').text();
                else if (companyId) unitKerja = $('#company option:selected').text();
                $('#unit-kerja-cell').text(unitKerja);

                $.ajax({
                    url: "{{ route('report.uar.job-roles') }}",
                    data: {
                        periode_id: periodeId,
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    beforeSend: () => $('#load-spinner').show(),
                    success: function(resp) {
                        jobRoleTable.clear();
                        if (resp.data && resp.data.length > 0) {
                            jobRoleTable.rows.add(resp.data).draw();
                            $('#jumlah-awal-user-cell').html('<strong>' + resp.data.length +
                                '</strong>');
                        } else {
                            jobRoleTable.draw();
                            $('#jumlah-awal-user-cell').html(
                                '<em style="color:#a00;">Belum ada user terdaftar. Silahkan cek konfigurasi Job Role - User ID</em>'
                            );
                        }
                        $('#nomor-surat-cell').text(resp.nomorSurat || 'XXX - Belum terdaftar');
                        $('#cost-center-cell').text(resp.cost_center || '-');

                        // User System Summary (unchanged)
                        const $ussContainer = $('#user-system-summary-container');
                        const $ussTbody = $('#user-system-summary-table tbody');
                        if (Array.isArray(resp.user_system) && resp.user_system.length > 0) {
                            let rowsHtml = '';
                            resp.user_system.forEach(u => {
                                rowsHtml += `<tr>
                                    <td>${u.user_code ?? '-'}</td>
                                    <td>${u.user_profile ?? '-'}</td>
                                    <td>${u.last_login ? u.last_login.substring(0,10) : '-'}</td>
                                </tr>`;
                            });
                            $ussTbody.html(rowsHtml);
                            $ussContainer.show();
                        } else {
                            $ussTbody.empty();
                            $ussContainer.hide();
                        }
                    },
                    error: function() {
                        jobRoleTable.draw();
                        $('#jumlah-awal-user-cell').html(
                            '<em style="color:#a00;">Belum ada user terdaftar. Silahkan cek konfigurasi Job Role - User ID</em>'
                        );
                    },
                    complete: () => $('#load-spinner').hide()
                });
            });

            $('#export-word').on('click', function(e) {
                e.preventDefault();
                let periodeId = $('#periode_id').val();
                if (!periodeId) {
                    Swal.fire('Periode belum dipilih', 'Silakan pilih periode terlebih dahulu', 'warning');
                    return;
                }
                let params = $.param({
                    periode_id: periodeId,
                    company_id: $('#company').val(),
                    kompartemen_id: $('#kompartemen').val(),
                    departemen_id: $('#departemen').val()
                });
                window.open("{{ route('report.uar.export-word') }}?" + params, '_blank');
            });
        });
    </script>
@endsection
