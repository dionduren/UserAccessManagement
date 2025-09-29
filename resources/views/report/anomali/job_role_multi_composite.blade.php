@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Job Role dengan Multiple Composite Role</h5>
            </div>
            <div class="card-body">
                <table id="tbl-jobrole-multi-comp" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Kompartemen</th>
                            <th>Departemen</th>
                            <th>Job Role ID</th>
                            <th>Job Role</th>
                            <th>Jumlah Composite Role Terpasang</th>
                            <th>List Composite Role</th>
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
            const $table = $('#tbl-jobrole-multi-comp');

            const filterRow = $('<tr class="table-secondary filters"></tr>');
            ['Company', 'Kompartemen', 'Departemen', 'Job Role ID', 'Job Role', 'Jumlah Composite Role Terpasang',
                'List Composite Role'
            ]
            .forEach(label => {
                filterRow.append(
                    `<th><input type="text" class="form-control form-control-sm" placeholder="Cari ${label}"></th>`
                );
            });
            $table.find('thead').append(filterRow);

            const dataTable = $table.DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('report.anomali.job-role-multi-composite') }}",
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
                        title: 'Job Role dengan Multiple Composite Role - {{ date('Y-m-d_H-i-s') }}'
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
                        data: 'job_role_id'
                    },
                    {
                        data: 'job_role_name'
                    },
                    {
                        data: 'composite_total'
                    },
                    {
                        data: 'composite_names'
                    },
                ],
                createdRow: function(_, data) {
                    $(this).attr('data-job-role-id', data.job_role_id);
                }
            });

            dataTable.columns().every(function(idx) {
                $('.filters th').eq(idx).find('input').on('keyup change', function() {
                    dataTable.column(idx).search(this.value).draw();
                });
            });
        });
    </script>
@endsection
