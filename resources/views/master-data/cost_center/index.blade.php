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
                    <th>Cost Center</th>
                    <th>Identifier</th>
                    <th>Deskripsi</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>

        <!-- DataTable -->
        {{-- <table id="cost_center_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>
                        <input type="text" id="search_company" class="form-control" placeholder="Search Perusahaan">
                    </th>
                    <th>
                        <input type="text" id="search_cost_center" class="form-control" placeholder="Search Cost Center">
                    </th>
                    <th>
                        <input type="text" id="search_valid_to" class="form-control" placeholder="Search Valid To">
                    </th>
                    <th>
                        <input type="text" id="search_pic_1" class="form-control" placeholder="Search PIC 1">
                    </th>
                    <th>
                        <input type="text" id="search_pic_2" class="form-control" placeholder="Search PIC 2">
                    </th>
                    <th>
                        <input type="text" id="search_pic_3" class="form-control" placeholder="Search PIC 3">
                    </th>
                    <th>
                        <input type="text" id="search_response" class="form-control" placeholder="Search Response">
                    </th>
                    <th>
                        <input type="text" id="search_remark" class="form-control" placeholder="Search Remark">
                    </th>
                    <th rowspan="3">Actions</th>
                </tr>
                <tr style="background-color: orange">
                    <th>Perusahaan</th>
                    <th>Cost Center</th>
                    <th>Valid To</th>
                    <th>Person in Charge</th>
                    <th>PIC Baru</th>
                    <th>PIC</th>
                    <th>Response</th>
                    <th>Remark</th>
                </tr>
                <tr style="background-color:azure">
                    <th>Perusahaan</th>
                    <th>Cost Center</th>
                    <th>Valid To</th>
                    <th>Penanggung Jawab CC</th>
                    <th>PIC Baru</th>
                    <th>PIC Sebelumnya</th>
                    <th>Surat Penunjukan</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PT Pupuk Indonesia</td>
                    <td>A110000OPR2</td>
                    <td>31.08.2024</td>
                    <td>Ka. Satuan Pengawasan Intern</td>
                    <td>Firdy Ikhwany</td>
                    <td>Satuan Pengawas Intern</td>
                    <td>26642/A/TI/A0102/IT/2024</td>
                    <td> - </td>
                </tr>
                <tr>
                    <td>PT Pupuk Indonesia</td>
                    <td>A3210000MGR</td>
                    <td>31.12.9999</td>
                    <td>SVP Pengadaan 1</td>
                    <td style="background-color: yellow">Dinonaktifkan</td>
                    <td>VP Pengadaan Barang</td>
                    <td>26000/A/TI/F0202/IT/2024</td>
                    <td>Nonaktif</td>
                </tr>
            </tbody>
        </table> --}}
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
                serverSide: true,
                ajax: "{{ route('cost-center.index') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'group',
                        name: 'group'
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
                    [3, 'asc']
                ]
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
