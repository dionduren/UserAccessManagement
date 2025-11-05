@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h4 class="mb-0 flex-grow-1">Relationship: User NIK &amp; Job Role</h4>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('nik-job.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Create Relationship
                    </a>
                    <div id="dtButtons" class="btn-group"></div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="periode" class="mb-0 small text-nowrap">Periode</label>
                        <select name="periode" id="periode" class="form-select form-select-sm">
                            <option value="">-- Pilih Periode --</option>
                            @foreach ($periodes as $periode)
                                <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped table-hover cell-border w-100" id="nik_job_role_table">
                    <thead>
                        <tr>
                            <th style="background-color: greenyellow">Perusahaan</th>
                            <th>User Group</th>
                            <th width="10%">NIK</th>
                            <th>Nama</th>
                            <th style="background-color: greenyellow">Unit Kerja</th>
                            <th>Job Role</th>
                            <th width="12.5%">Action</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" type="text" class="form-control form-control-sm"
                                    placeholder="Perusahaan"></th>
                            <th><input data-col="1" type="text" class="form-control form-control-sm"
                                    placeholder="User Group"></th>
                            <th><input data-col="2" type="text" class="form-control form-control-sm" placeholder="NIK">
                            </th>
                            <th><input data-col="3" type="text" class="form-control form-control-sm"
                                    placeholder="Nama"></th>
                            <th><input data-col="4" type="text" class="form-control form-control-sm"
                                    placeholder="Unit Kerja"></th>
                            <th><input data-col="5" type="text" class="form-control form-control-sm"
                                    placeholder="Job Role"></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script>
        let nikJobRoleTable;

        function initNIKJobRoleTable() {
            if (!nikJobRoleTable) {
                nikJobRoleTable = $('#nik_job_role_table').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: '{{ route('nik-job.get-by-periode') }}',
                        data: function(d) {
                            d.periode_id = $('#periode').val();
                        },
                    },
                    columns: [{
                            data: 'company',
                            name: 'company'
                        },
                        {
                            data: 'user_group',
                            name: 'user_group'
                        },
                        {
                            data: 'nik',
                            name: 'nik'
                        },
                        {
                            data: 'nama',
                            name: 'nama'
                        },
                        {
                            data: 'kompartemen',
                            name: 'kompartemen'
                        },
                        {
                            data: 'job_role',
                            name: 'job_role'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    responsive: true,
                    searching: true,
                    paging: true,
                    ordering: true,
                    pageLength: 10,
                    lengthMenu: [
                        [5, 10, 25, 50, 100, -1],
                        [5, 10, 25, 50, 100, 'All']
                    ],
                    order: [
                        [1, 'asc']
                    ],
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
                        title: 'NIK_Job_Role',
                        filename: 'NIK_Job_Role_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    }],
                    initComplete: function() {
                        const api = this.api();
                        const wrapper = $(api.table().container());

                        // Move built-in controls to layout slots
                        wrapper.find('.dt-length').appendTo(wrapper.find('.pageLength').first());
                        wrapper.find('.dt-search').appendTo(wrapper.find('.search').first());
                        wrapper.find('.dt-info').appendTo(wrapper.find('.info').first());
                        wrapper.find('.dt-paging').appendTo(wrapper.find('.paging').first());

                        // Excel button in card header
                        api.buttons().container().appendTo('#dtButtons');

                        // Column filters
                        $('#nik_job_role_table thead tr.filters input').on('keyup change', function() {
                            const colIdx = $(this).data('col');
                            const val = this.value;
                            if (api.column(colIdx).search() !== val) {
                                api.column(colIdx).search(val).draw();
                            }
                        });
                    }
                });
            }
        }

        function loadNIKJobRoleTable() {
            if (nikJobRoleTable) {
                $('#nik_job_role_table').show();
                nikJobRoleTable.ajax.reload();
            }
        }

        $('#periode').on('change', function() {
            initNIKJobRoleTable();
            loadNIKJobRoleTable();
        });
    </script>
@endsection

@section('header-scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endsection

@section('scripts')
    <!-- Export to Excel deps -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
@endsection
