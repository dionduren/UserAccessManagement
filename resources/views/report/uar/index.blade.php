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
                        <td>Jumlah User Dihapus</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jumlah User Baru</td>
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
            <table id="job-role-table" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>User ID</th>
                        <th width="20%">Nama</th>
                        <th width="20%">Job Role</th>
                        {{-- <th width="20%" style="background-color: #d4edda;">Kompartemen</th> --}}
                        {{-- <th width="20%" style="background-color: #d4edda;">Departemen</th> --}}
                        <th width="7,5%">NIK</th>
                        <th style="background-color: yellowgreen">User ID (Middle DB)</th>
                        <th style="background-color: yellowgreen">Nama (Middle DB)</th>
                        <th style="background-color: orange">NIK (Master Karyawan)</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
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
                ]
            });

            $('#load').on('click', function() {
                let periodeId = $('#periode_id').val();
                if (!periodeId) {
                    Swal.fire('Periode belum dipilih', 'Silakan pilih periode terlebih dahulu', 'warning');
                    return;
                }

                $('#review-table-container').show();
                $('#job-role-table-container').show();
                $('#export-word').show();

                let companyId = $('#company').val();
                let kompartemenId = $('#kompartemen').val();
                let departemenId = $('#departemen').val();

                // Tentukan Unit Kerja
                let unitKerja = '-';
                if (departemenId) {
                    unitKerja = $('#departemen option:selected').text();
                } else if (kompartemenId) {
                    unitKerja = $('#kompartemen option:selected').text();
                } else if (companyId) {
                    unitKerja = $('#company option:selected').text();
                }
                $('#unit-kerja-cell').text(unitKerja);

                $.ajax({
                    url: "{{ route('report.uar.job-roles') }}",
                    data: {
                        periode_id: periodeId,
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    success: function(resp) {
                        jobRoleTable.clear();
                        if (resp.data) {
                            jobRoleTable.rows.add(resp.data).draw();
                            $('#jumlah-awal-user-cell').html('<strong>' + resp.data.length +
                                '</strong>');
                        } else {
                            jobRoleTable.draw();
                            $('#jumlah-awal-user-cell').html('<strong>0</strong>');
                        }
                        $('#nomor-surat-cell').text(resp.nomorSurat || 'XXX - Belum terdaftar');
                        $('#cost-center-cell').text(resp.cost_center || '-');
                    }
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
