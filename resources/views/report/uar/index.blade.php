@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h3>User Access Management Report</h3>
        <div class="row mb-3">
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
                        <td width="30%">Aset Informasi</td>
                        <td>User ID SAP</td>
                    </tr>
                    <tr>
                        <td>Unit Kerja</td>
                        <td id="unit-kerja-cell">-</td>
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
                        <th width="20%" style="background-color: #d4edda;">Kompartemen</th>
                        <th width="20%" style="background-color: #d4edda;">Departemen</th>
                        <th width="15%">NIK PIC</th>
                        <th>Tetap</th>
                        <th>Berubah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Cascading dropdowns
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

            $('#departemen').on('change', function() {
                console.log($(this).val());
            });

            // Initialize DataTable without ajax
            var jobRoleTable = $('#job-role-table').DataTable({
                processing: true,
                serverSide: false,
                data: [],
                columns: [{
                        data: null,
                        name: 'index',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'user_nik',
                        name: 'user_nik'
                    },
                    {
                        data: 'user_definisi',
                        name: 'user_definisi'
                    },
                    {
                        data: 'job_role',
                        name: 'job_role'
                    },
                    {
                        data: 'kompartemen',
                        name: 'kompartemen'
                    },
                    {
                        data: 'departemen',
                        name: 'departemen'
                    },
                    {
                        data: null,
                        name: 'nik_pic',
                        defaultContent: '',
                        render: function() {
                            return '';
                        }
                    },
                    {
                        data: null,
                        name: 'tetap',
                        defaultContent: '',
                        render: function() {
                            return '';
                        }
                    },
                    {
                        data: null,
                        name: 'berubah',
                        defaultContent: '',
                        render: function() {
                            return '';
                        }
                    },
                    {
                        data: null,
                        name: 'keterangan',
                        defaultContent: '',
                        render: function() {
                            return '';
                        }
                    }
                ]
            });

            // Load data on button click
            $('#load').on('click', function() {
                // Show tables
                $('#review-table-container').show();
                $('#job-role-table-container').show();
                $('#export-word').show();

                // Get selected values
                let companyId = $('#company').val();
                let companyName = $('#company option:selected').text();
                let kompartemenId = $('#kompartemen').val();
                let kompartemenName = $('#kompartemen option:selected').text();
                let departemenId = $('#departemen').val();
                let departemenName = $('#departemen option:selected').text();

                // Determine Unit Kerja
                let unitKerja = '-';
                if (departemenId) {
                    let displayName = departemenName;
                    if (displayName.startsWith('Dep.')) {
                        displayName = displayName.replace(/^Dep\.\s*/, '');
                        unitKerja = 'Departemen ' + displayName;
                    } else {
                        unitKerja = departemenName;
                    }
                } else if (kompartemenId) {
                    let displayName = kompartemenName;
                    // Remove "Komp." if present at the start
                    if (displayName.startsWith('Komp.')) {
                        displayName = displayName.replace(/^Komp\.\s*/, '');
                        unitKerja = 'Kompartemen ' + displayName;
                    } else if (displayName.startsWith('Fungs.')) {
                        displayName = displayName.replace(/^Fungs\.\s*/, '');
                        unitKerja = 'Fungsional ' + displayName;
                    } else {
                        unitKerja = displayName;
                    }
                } else if (companyId) {
                    unitKerja = companyName;
                }

                $('#unit-kerja-cell').text(unitKerja);

                // Load job roles and count users
                $.ajax({
                    url: "{{ route('report.uar.job-roles') }}",
                    data: {
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    success: function(response) {
                        jobRoleTable.clear();
                        let totalUser = 0;
                        if (response.data) {
                            jobRoleTable.rows.add(response.data).draw();
                            // Sum user count from response
                            totalUser = response.data.length;
                        } else {
                            jobRoleTable.draw();
                        }
                        $('#jumlah-awal-user-cell').html('<strong>' + totalUser +
                            '</strong>');
                    }
                });
            });

            $('#export-word').on('click', function(e) {
                e.preventDefault();
                // Pass current filter as query string
                let params = $.param({
                    company_id: $('#company').val(),
                    kompartemen_id: $('#kompartemen').val(),
                    departemen_id: $('#departemen').val()
                });
                window.open("{{ route('report.uar.export-word') }}?" + params, '_blank');
            });
        });
    </script>
@endsection
