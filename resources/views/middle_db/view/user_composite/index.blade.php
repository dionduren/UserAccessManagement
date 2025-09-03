@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User - Composite Role</h5>
                <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
            </div>
            <div class="card-body">
                <table id="tblUserComposite" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>SAP User</th>
                            <th>Composite Role</th>
                        </tr>
                        <tr class="filters">
                            <th><input data-col="0" class="form-control form-control-sm" placeholder="SAP User"></th>
                            <th><input data-col="1" class="form-control form-control-sm" placeholder="Composite"></th>
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
        document.addEventListener('DOMContentLoaded', () => {
            const table = $('#tblUserComposite').DataTable({
                processing: true,
                pageLength: 25,
                orderCellsTop: true,
                order: [
                    [0, 'asc']
                ],
                ajax: '{{ route('middle_db.view.uam.user_composite.data') }}',
                columns: [{
                    data: 'sap_user'
                }, {
                    data: 'composite_role'
                }],
                initComplete() {
                    $('#tblUserComposite thead tr.filters input').on('keyup change', function() {
                        const c = $(this).data('col'),
                            v = this.value;
                        if (table.column(c).search() !== v) {
                            table.column(c).search(v).draw();
                        }
                    });
                }
            });
            document.getElementById('btnReload').addEventListener('click', () => {
                $('#tblUserComposite thead tr.filters input').val('').each(function() {
                    table.column($(this).data('col')).search('');
                });
                table.ajax.reload(null, false);
            });
        });
    </script>
@endsection
