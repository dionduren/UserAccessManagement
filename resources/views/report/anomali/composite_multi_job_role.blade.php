@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Composite Role dengan Multiple Job Role</h5>
            </div>
            <div class="card-body">
                <table id="tbl-comp-multi-jobrole" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Composite Name</th>
                            <th>Total Job Role</th>
                            <th>Job Role Codes</th>
                            <th>Job Role Names</th>
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
            const $table = $('#tbl-comp-multi-jobrole');
            const headers = ['Composite Name', 'Company', 'Total Job Role', 'Job Role Codes', 'Job Role Names'];
            const filterRow = $('<tr class="table-secondary filters"></tr>');

            headers.forEach(label => {
                filterRow.append(
                    `<th><input type="text" class="form-control form-control-sm" placeholder="Cari ${label}"></th>`
                );
            });

            $table.find('thead').append(filterRow);

            const dataTable = $table.DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('report.anomali.composite-multi-jobrole') }}",
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
                        title: 'Composite Role dengan Multiple Job Role - {{ date('Y-m-d_H-i-s') }}'
                    },
                    {
                        extend: 'copy',
                        className: 'btn btn-outline-secondary btn-sm',
                        text: '<i class="bi bi-clipboard"></i> Copy'
                    }
                ],
                columns: [{
                        data: 'company'
                    }, {
                        data: 'composite_name'
                    },
                    {
                        data: 'job_role_total'
                    },
                    {
                        data: 'job_role_codes'
                    },
                    {
                        data: 'job_role_names'
                    },
                ]
            });

            dataTable.columns().every(function(idx) {
                $('.filters th').eq(idx).find('input').on('keyup change', function() {
                    dataTable.column(idx).search(this.value).draw();
                });
            });
        });
    </script>
@endsection
