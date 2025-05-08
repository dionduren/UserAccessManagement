@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Dashboard Cost Center</h1>

        <a href="{{ route('cost-center.create') }}" target="_blank" class="btn btn-primary mb-3">
            <i class="bi bi-plus"></i> Create New User Cost Center
        </a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <table id="cost_center_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Perusahaan</th>
                    <th>Tipe Unit Kerja</th>
                    <th>ID Unit Kerja</th>
                    <th>Nama Unit Kerja</th>
                    <th>Cost Center</th>
                    <th>Identifier</th>
                    <th>Deskripsi</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="costCenterModal" tabindex="-1" aria-labelledby="costCenterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="costCenterModalLabel">Cost Center Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-cost-center-details">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let masterData = {}; // Store parsed JSON for efficient lookups

            let costCenterTable = $('#cost_center_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('cost-center.index') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'company_id',
                        name: 'Perusahaan'
                    },
                    {
                        data: 'level',
                        name: 'level'
                    },
                    {
                        data: 'level_id',
                        name: 'level_id'
                    },
                    {
                        data: 'level_name',
                        name: 'level_name'
                    },
                    {
                        data: 'cost_center',
                        name: 'cost_center'
                    },
                    {
                        data: 'cost_code',
                        name: 'cost_code'
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi'
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
                lengthMenu: [5, 10, 25, 50, 100],
                columnDefs: [{
                    targets: [0],
                    visible: false
                }],
                order: [
                    [6, 'asc']
                ],
                dom: 'B<"row justify-content-between"<"col-auto"l><"col text-end"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 text-end"p>>',
                buttons: [{
                    extend: 'excel',
                    title: 'Master Data Cost Center {{ date('Y-m-d H:i:s') }}',
                    text: '<i class="bi bi-file-earmark-excel"></i> Export to Excel',
                    className: 'btn btn-success'
                }]
            });
        });

        // ✅ SweetAlert2 Delete Confirmation
        function deleteCostCenter(id) {
            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Anda tidak bisa mengembalikan data yang dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Tidak Jadi"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/cost-center/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Deleted!",
                                text: response.success,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1500
                            });

                            // Find the row to remove
                            let row = $('#cost_center_table').DataTable().row($(
                                `button[data-id="${id}"]`).parents('tr'));

                            // Remove the row and redraw the table
                            row.remove().draw();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to delete record.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
