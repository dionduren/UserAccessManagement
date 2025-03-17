@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Dashboard User NIK</h1>

        {{-- <a href="{{ route('user-nik.create') }}" target="_blank" class="btn btn-outline-secondary mb-3"> --}}
        <a class="btn btn-outline-secondary mb-3" disabled>
            <i class="bi bi-plus"></i> Buat User NIK Baru
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

        <table id="user_nik_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Perusahaan</th>
                    <th>NIK</th>
                    <th style="background-color:greenyellow">Nama</th>
                    <th style="background-color: greenyellow">Direktorat</th>
                    <th style="background-color: lightblue">Kompartemen</th>
                    <th style="background-color: greenyellow">Cost Center</th>
                    {{-- <th style="background-color: greenyellow">Grade</th> --}}
                    <th>Tipe Lisensi</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="userNIKModal" tabindex="-1" aria-labelledby="userNIKModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userNIKModalLabel">User NIK Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-user-nick-details">
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

            let userNikTable = $('#user_nik_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user-nik.index_mixed') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
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
                        name: 'nama'
                    },
                    {
                        data: 'direktorat',
                        name: 'direktorat'
                    },
                    {
                        data: 'kompartemen_name',
                        name: 'kompartemen_name'
                    },
                    {
                        data: 'cost_center',
                        name: 'cost_center'
                    },
                    // {
                    //     data: 'grade',
                    //     name: 'grade'
                    // },
                    {
                        data: 'license_type',
                        name: 'license_type'
                    },
                    {
                        data: 'valid_from',
                        name: 'valid_from'
                    },
                    {
                        data: 'valid_to',
                        name: 'valid_to'
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
                    [2, 'asc']
                ]
            });

        });

        // ✅ SweetAlert2 Delete Confirmation
        function deleteUserNIK(id) {
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
                        url: '/user-nik/' + id,
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
                            let row = $('#user_nik_table').DataTable().row($(
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
