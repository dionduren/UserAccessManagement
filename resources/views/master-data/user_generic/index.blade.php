@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Dashboard User Generic</h1>

        {{-- <a href="{{ route('user-generic.create') }}" target="_blank" class="btn btn-outline-secondary mb-3"> --}}
        <a class="btn btn-outline-secondary mb-3" disabled>
            <i class="bi bi-plus"></i> Buat User Generic Baru
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

        <div class="form-group">
            <label for="periode">Periode</label>
            <select name="periode" id="periode" class="form-control">
                <option value="">Silahkan Pilih Periode Data</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                @endforeach
            </select>
        </div>

        <table id="user_generic_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th style="width: 5%;">Perusahaan</th>
                    {{-- <th style="width: 9%;">Kompartemen ID</th> --}}
                    <th style="width: 10%;">Kompartemen Name</th>
                    {{-- <th style="width: 8%;">Departemen ID</th> --}}
                    <th style="width: 10%;">Departemen Name</th>
                    {{-- <th style="width: 12%;">Periode</th> --}}
                    <th style="width: 8%;">User Code</th>
                    {{-- <th style="width: 12%;">Cost Code</th> --}}
                    <th style="width: 8%;">User Profile</th>
                    {{-- <th style="width: 10%;">User Profile</th> --}}
                    {{-- <th>PIC</th>
                    <th>Unit Kerja</th>
                    <th>Job Role</th> --}}
                    <th style="width: 7%;">Tipe Lisensi</th>
                    <th style="width: 7%;">Valid From</th>
                    <th style="width: 7%;">Valid To</th>
                    <th style="width: 10%;">Last Login</th>
                    <th style="width: 5%;">Flagged</th>
                    <th style="width: 12%;">Action</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable with no ajax source
            var userGenericTable = $('#user_generic_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: false, // Don't load data initially
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'group',
                        name: 'group'
                    },
                    // {
                    //     data: 'kompartemen_id',
                    //     name: 'kompartemen_id'
                    // },
                    {
                        data: 'kompartemen_name',
                        name: 'kompartemen_name'
                    },
                    // {
                    //     data: 'departemen_id',
                    //     name: 'departemen_id'
                    // },
                    {
                        data: 'departemen_name',
                        name: 'departemen_name'
                    },
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'user_profile',
                        name: 'user_profile'
                    },
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
                        data: 'last_login',
                        name: 'last_login',
                    },
                    {
                        data: 'flagged',
                        name: 'flagged',
                        render: function(data, type, row) {
                            return (data == 1) ? '<span class="badge bg-danger">Flagged</span>' :
                                '';
                        },
                        orderable: true,
                        searchable: false
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
                // order: [
                //     [8, 'desc']
                // ]
            });

            // Only load data when periode is selected
            $('#periode').on('change', function() {
                var periodeId = $(this).val();
                if (periodeId) {
                    userGenericTable.ajax.url("{{ route('user-generic.index') }}?periode=" + periodeId)
                        .load();
                } else {
                    userGenericTable.clear().draw(); // Clear table if no periode selected
                }
            });
        });

        // ✅ SweetAlert2 Delete Confirmation
        function deleteUserGeneric(id) {
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
                        url: '/cost-center/user-generic/' + id,
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
