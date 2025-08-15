@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Dashboard Cost Center</h2>
            </div>
            <div class="card-body">

                <a href="{{ route('cost-center.create') }}" target="_blank" class="btn btn-primary mb-3">
                    <i class="bi bi-plus"></i> Create New User Cost Center
                </a>

                <!-- Success Message -->
                @if (session('success'))
                    <div class="alert alert-success">
                        <h4>Success:</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col">
                        <label for="company_id">Pilih Perusahaan:</label>
                        <select name="company_id" id="company_id" class="form-control mb-3">
                            <option value="">-- Pilih Perusahaan --</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

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
        </div>
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
            let costCenterTable = $('#cost_center_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: function(data, callback, settings) {
                    let companyId = $('#company_id').val();
                    let url = "{{ route('cost-center.index') }}";
                    if (companyId) {
                        url += '?company_id=' + encodeURIComponent(companyId);
                    }
                    $.get(url, function(json) {
                        callback(json);
                    });
                },
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

            // Reload table when company_id changes
            $('#company_id').on('change', function() {
                costCenterTable.ajax.reload();
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
