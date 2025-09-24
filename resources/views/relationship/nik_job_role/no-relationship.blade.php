@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>NIK Tanpa Relasi Job Role</h1>

        <div class="form-group">
            <label for="periode">Periode</label>
            <select name="periode" id="periode" class="form-control">
                <option value="">-- Pilih Periode --</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <table id="nik_no_jobrole_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Perusahaan</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Kompartemen</th>
                    <th>Departemen</th>
                    <th>Wrong Job Role ID</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#nik_no_jobrole_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: false, // Don't load data initially
                columns: [{
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'group',
                        name: 'group'
                    },
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'nama',
                        name: 'user_details.nama'
                    },
                    {
                        data: 'kompartemen',
                        name: 'kompartemen.nama'
                    },
                    {
                        data: 'departemen',
                        name: 'departemen.nama'
                    },
                    {
                        data: 'wrong_job_role_id',
                        name: 'wrong_job_role_id',
                        defaultContent: ''
                    }
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });

            $('#periode').on('change', function() {
                var periode = $(this).val();
                if (periode) {
                    table.ajax.url("{{ route('nik-job.null-relationship') }}?periode=" + periode).load();
                } else {
                    table.clear().draw();
                }
            });
        });
    </script>
@endsection
