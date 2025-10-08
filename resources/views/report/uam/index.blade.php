@extends('layouts.app')

@section('header-scripts')
    <style>
        .collapse:not(.show) {
            display: none;
        }

        .collapsing {
            height: 0;
            overflow: hidden;
            transition: height 0.35s ease;
        }
    </style>
@endsection

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

    <div class="container-fluid mb-5">
        <h3>User Access Matrix Report</h3>
        <div class="row mb-3">
            <div class="col">
                <label>Periode</label>
                <select name="periode" id="periode" class="form-control">
                    <option value="">-- Select Periode --</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}"
                            {{ (int) $selectedPeriodeId === $periode->id ? 'selected' : '' }}>
                            {{ $periode->definisi }}
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

        {{-- BUTTONS --}}
        <div class="row mb-3">
            <div class="col-auto">
                <button id="load" class="btn btn-primary mb-3">Load Data</button>
            </div>
            <div class="col-auto">
                <button id="export-word" class="btn btn-info mb-3 text-white" style="display: none"><i
                        class="bi bi-file-earmark-word"></i>Export
                    to Word</button>
            </div>
            {{-- <div class="col-auto">
                <button id="export-composite-excel" class="btn btn-success mb-3" style="display: none"><i
                        class="bi bi-file-earmark-x"></i> Export Composite-Single to Excel</button>
            </div> --}}
            <div class="col-auto">
                <button id="export-single-excel" class="btn btn-success mb-3" style="display: none"><i
                        class="bi bi-file-earmark-x"></i> Export Single-Tcode to Excel</button>
            </div>
            <div class="col-auto">
                <button id="export-composite-no-ao" class="btn btn-warning mb-3 text-dark" style="display: none">
                    <i class="bi bi-exclamation-triangle"></i> Export Excel Composite tanpa Relationship
                </button>
            </div>
            <div class="col-auto align-self-center">
                <div id="load-spinner" class="spinner-border text-success" role="status" style="display:none;">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>

        {{-- RESULT 1: DOKUMEN REVIEW USER ID DAN OTORISASI --}}

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
                        <td>Unit Kerja</td>
                        <td id="unit-kerja-cell">-</td>
                    </tr>
                    <tr>
                        <td>Jumlah Unique Single Role</td>
                        <td id="unique-single-role-cell">-</td>
                    </tr>
                    <tr>
                        <td>Jumlah Unique Tcode</td>
                        <td id="unique-tcode-cell">-</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-2"></div>
        </div>

        <div id="job-role-table-container" class="mt-4" style="display:none;">
            <h3>1. Table Mapping Job Function & Composite Role</h3>
            <table id="job-role-table" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Job Role</th>
                        <th width="30%">Composite Role</th>
                        <th width="30%">Authorization Object</th>
                        <th width="10%">Sumber</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div id="composite-role-table-container" class="mt-4" style="display:none;">

            <h3>2. Table Composite Role - Single Role</h3>
            <!-- Composite Role Table Collapse -->
            <button class="btn btn-info my-2" id="hideCompositeRoleSingle" type="button" data-bs-toggle="collapse"
                data-bs-target="#compositeRoleCollapse" aria-expanded="false" aria-controls="compositeRoleCollapse">
                Show/Hide Table Composite Role - Single Role
            </button>
            {{-- Composite Role - Single Role Table --}}
            <div class="collapse" id="compositeRoleCollapse">
                <table id="composite-role-table" class="table table-bordered table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Composite Role</th>
                            <th>Single Roles</th>
                            <th>Deskripsi</th>
                            <th width="10%">Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="single-role-table-container" class="mt-4" style="display:none;">

            <h3>3. Table Single Role - Tcode</h3>
            <!-- Single Role Table Collapse -->
            <button class="btn btn-info my-2" id="hideSingleRoleTcode" type="button" data-bs-toggle="collapse"
                data-bs-target="#singleRoleCollapse" aria-expanded="false" aria-controls="singleRoleCollapse">
                Show/Hide Table Single Role - Tcode
            </button>
            {{-- Single Role - Tcode Table --}}
            <div class="collapse" id="singleRoleCollapse">
                <table id="single-role-table" class="table table-bordered table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Single Role</th>
                            <th>Tcode</th>
                            <th>Deskripsi Tcode</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
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

            // $('#departemen').on('change', function() {
            //     // console.log($(this).val());
            // });

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
                        data: 'job_role',
                        name: 'job_role'
                    },
                    {
                        data: 'composite_role',
                        name: 'composite_role'
                    },
                    {
                        data: 'authorization_object',
                        name: 'authorization_object'
                    },
                    {
                        data: 'source',
                        name: 'source',
                        title: 'Sumber',
                        render: function(data, type, row, meta) {
                            if (type === 'display' || type === 'filter') {
                                if (data === 'import') return 'MDB';
                                if (data === 'manual' || data === 'edit') return 'SYS';
                                if (data === 'upload') return 'UPL';
                                return data ?? '';
                            }
                            return data;
                        }
                    }
                ],
                tooltips: true,
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50, 100],
                order: [
                    [0, 'asc']
                ]
            });

            // Load data on button click
            $('#load').on('click', function() {
                @if (is_null($activePeriode))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Periode belum dibuat, Laporan tidak bisa digenerate',
                    });
                    return;
                @endif

                // Show tables
                $('#review-table-container').show();
                $('#job-role-table-container').show();
                $('#export-word').show();
                // $('#export-composite-excel').show();
                $('#export-single-excel').show();
                $('#export-composite-no-ao').show();


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
                    url: "{{ route('report.uam.job-roles') }}",
                    data: {
                        periode_id: $('#periode').val(),
                        company_id: companyId,
                        kompartemen_id: kompartemenId,
                        departemen_id: departemenId
                    },
                    beforeSend: function() {
                        $('#load-spinner').show();
                    },
                    success: function(response) {
                        jobRoleTable.clear();

                        const jobRolesEmpty = !Array.isArray(response.data) || response.data
                            .length === 0;
                        let totalUser = 0;
                        let cost_center = response.cost_center || '-';

                        if (jobRolesEmpty) {
                            jobRoleTable.draw();
                            $('#job-role-table-container').hide();
                            $('#nomor-surat-cell').text('XXX - Belum terdaftar');
                        } else {
                            jobRoleTable.rows.add(response.data).draw();
                            totalUser = response.data.length;
                            cost_center = response.cost_center;
                            $('#nomor-surat-cell').text(response.nomorSurat ||
                                'XXX - Belum terdaftar');
                            $('#job-role-table-container').show();
                        }

                        $('#jumlah-awal-user-cell').html('<strong>' + totalUser + '</strong>');
                        $('#cost-center-cell').text(cost_center ?? '-');

                        if (!jobRolesEmpty && response.composite_roles && response
                            .composite_roles.length > 0) {
                            let tbody = '';
                            response.composite_roles.forEach(function(cr, idx) {
                                const singleRoles = cr.single_roles;
                                singleRoles.forEach(function(sr, srIdx) {
                                    tbody += '<tr>';
                                    if (srIdx === 0) {
                                        tbody +=
                                            `<td rowspan="${singleRoles.length}">${idx + 1}</td>`;
                                        tbody +=
                                            `<td rowspan="${singleRoles.length}">${cr.nama_display}</td>`;
                                    }
                                    tbody += `<td>${sr.nama_display}</td>`;
                                    tbody += `<td>${sr.deskripsi}</td>`;
                                    let srcDisplay = sr.source === 'import' ?
                                        'MDB' :
                                        (sr.source === 'manual' || sr.source ===
                                            'edit') ? 'SYS' :
                                        sr.source === 'upload' ? 'UPL' :
                                        (sr.source || 'CLOUD');
                                    tbody += `<td>${srcDisplay}</td>`;
                                    tbody += '</tr>';
                                });
                            });
                            $('#composite-role-table tbody').html(tbody);
                            $('#composite-role-table-container').show();
                        } else {
                            $('#composite-role-table tbody').html('');
                            $('#composite-role-table-container').hide();
                        }

                        if (!jobRolesEmpty && response.single_roles && response.single_roles
                            .length > 0) {
                            let tbody = '';
                            let idx = 1;
                            const tcodeSet = new Set();

                            response.single_roles.forEach(function(sr) {
                                if (sr.tcodes.length > 0) {
                                    sr.tcodes.forEach(function(tc, tcIdx) {
                                        tbody += '<tr>';
                                        if (tcIdx === 0) {
                                            tbody +=
                                                `<td rowspan="${sr.tcodes.length}">${idx}</td>`;
                                            tbody +=
                                                `<td rowspan="${sr.tcodes.length}">${sr.nama_display}</td>`;
                                        }
                                        tbody += `<td>${tc.tcode_display}</td>`;
                                        tbody +=
                                            `<td>${tc.deskripsi || '-'}</td>`;
                                        tbody += '</tr>';

                                        if (tc.tcode) tcodeSet.add(tc.tcode);
                                    });
                                } else {
                                    tbody += `<tr>
                                        <td>${idx}</td>
                                        <td>${sr.nama}</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>`;
                                }
                                idx++;
                            });

                            $('#single-role-table tbody').html(tbody);
                            $('#single-role-table-container').show();
                            $('#unique-single-role-cell').text(response.single_roles.length);
                            $('#unique-tcode-cell').text(tcodeSet.size);
                        } else {
                            $('#single-role-table tbody').html('');
                            $('#single-role-table-container').hide();
                            $('#unique-single-role-cell').text('0');
                            $('#unique-tcode-cell').text('0');
                        }

                        if (jobRolesEmpty) {
                            $('#unique-single-role-cell').html(
                                '<em style="color:#a00;">Belum ada user terdaftar. Silahkan cek konfigurasi Job Role - Composite Role, serta Relationship Composite Role - Single Role</em>'
                            );
                            $('#unique-tcode-cell').text('0');
                        }
                    },
                    error: function() {
                        jobRoleTable.draw();
                        $('#job-role-table-container').hide();
                        $('#composite-role-table tbody').html('');
                        $('#composite-role-table-container').hide();
                        $('#single-role-table tbody').html('');
                        $('#single-role-table-container').hide();
                        $('#jumlah-awal-user-cell').html(
                            '<em style="color:#a00;">Belum ada user terdaftar. Silahkan cek konfigurasi Job Role - Composite Role, serta Relationship Composite Role - Single Role</em>'
                        );
                        $('#unique-single-role-cell').html(
                            '<em style="color:#a00;">Belum ada user terdaftar. Silahkan cek konfigurasi Job Role - Composite Role, serta Relationship Composite Role - Single Role</em>'
                        );
                        $('#unique-tcode-cell').text('0');
                        $('#cost-center-cell').text('-');
                        $('#nomor-surat-cell').text('XXX - Belum terdaftar');
                    },
                    complete: function() {
                        $('#load-spinner').hide();
                    }
                });
            });

            $('#export-word').on('click', function(e) {
                e.preventDefault();
                let params = $.param({
                    periode_id: $('#periode').val(),
                    company_id: $('#company').val(),
                    kompartemen_id: $('#kompartemen').val(),
                    departemen_id: $('#departemen').val()
                });
                window.open("{{ route('report.uam.export-word') }}?" + params, '_blank');
            });

            $('#export-single-excel').on('click', function(e) {
                e.preventDefault();
                let params = $.param({
                    periode_id: $('#periode').val(),
                    company_id: $('#company').val(),
                    kompartemen_id: $('#kompartemen').val(),
                    departemen_id: $('#departemen').val()
                });
                window.open("{{ route('report.uam.export-single-excel') }}?" + params, '_blank');
            });

            $('#export-composite-no-ao').on('click', function(e) {
                e.preventDefault();
                let params = $.param({
                    periode_id: $('#periode').val(),
                    company_id: $('#company').val(),
                    kompartemen_id: $('#kompartemen').val(),
                    departemen_id: $('#departemen').val()
                });
                window.open("{{ route('report.uam.export-composite-no-ao') }}?" + params, '_blank');
            });
        });
    </script>
@endsection
