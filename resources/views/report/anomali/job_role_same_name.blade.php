@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Job Role dengan Nama Ganda</h5>
            </div>
            <div class="card-body">
                <table id="tbl-jobrole-same-name" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Kompartemen</th>
                            <th>Departemen</th>
                            <th>Job Role Name</th>
                            <th>Job Role Code</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            const $table = $('#tbl-jobrole-same-name');

            const dt = $table.DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('report.anomali.job-role-same-name') }}",
                    beforeSend: xhr => xhr.setRequestHeader('Accept', 'application/json')
                },
                lengthMenu: [10, 25, 50, 100],
                pageLength: 25,
                orderCellsTop: true,
                dom: '<"row mb-2"<"col-md-6"l><"col-md-6 text-md-end"B>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-outline-primary btn-sm',
                        text: '<i class="bi bi-download"></i> Export',
                        title: 'Job Role dengan Nama Ganda - {{ date('Y-m-d_H-i-s') }}'
                    },
                    {
                        extend: 'copy',
                        className: 'btn btn-outline-secondary btn-sm',
                        text: '<i class="bi bi-clipboard"></i> Copy'
                    }
                ],
                columns: [{
                        data: 'company'
                    },
                    {
                        data: 'kompartemen'
                    },
                    {
                        data: 'departemen'
                    },
                    {
                        data: 'job_role_name'
                    },
                    {
                        data: 'job_role_code'
                    },
                ]
            });
        });
    </script>
@endsection
