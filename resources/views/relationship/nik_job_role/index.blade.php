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
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>NIK Job Role Relationship</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('nik-job.create') }}" class="btn btn-primary mb-3">
                            <i class="bi bi-plus"></i> Create Relationship
                        </a>

                        <div class="form-group">
                            <label for="periode">Periode</label>
                            <select name="periode" id="periode" class="form-control">
                                <option value="">-- Pilih Periode --</option>
                                @foreach ($periodes as $periode)
                                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <table class="table table-bordered mt-3" id="nik_job_role_table">
                            <thead>
                                <tr>
                                    <th style="background-color: red;color:white">Periode (TO BE DELETED)</th>
                                    <th style="background-color: greenyellow">Perusahaan</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th style="background-color: greenyellow">Unit Kerja</th>
                                    <th>Job Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
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
                            data: 'periode',
                            name: 'periode'
                        },
                        {
                            data: 'company',
                            name: 'company'
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
                    ]
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
