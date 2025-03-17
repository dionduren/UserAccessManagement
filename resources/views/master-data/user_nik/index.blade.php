@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Dashboard User NIK</h1>

        {{-- <a href="{{ route('user-nik.create') }}" target="_blank" class="btn btn-outline-secondary mb-3"> --}}
        <a href="{{ route('user-nik.upload-page') }}" class="btn btn-outline-primary mb-3">
            <i class="bi bi-upload"></i> Upload User NIK
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

        <table id="user_nik_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead style="vertical-align: middle;">
                <tr>
                    <th>id</th>
                    <th>Perusahaan</th>
                    <th>Periode</th>
                    <th>NIK</th>
                    <th>Tipe Lisensi</th>
                    <th>Login Terakhir</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <div id="modal-user-nick-details"></div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let masterData = {}; // Store parsed JSON for efficient lookups


            // Listen to select change event
            $('#periode').on('change', function(e) {
                let periodeId = $(this).val();

                if (periodeId) {
                    $('#user_nik_table').DataTable().ajax.url(
                        "{{ route('user-nik.index') }}" + "?periode=" + periodeId).load();
                } else {
                    $('#user_nik_table').DataTable().ajax.url(
                        "{{ route('user-nik.index') }}").load();
                }
            });

            let userNikTable = $('#user_nik_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user-nik.index') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'group',
                        name: 'group'
                    },
                    {
                        data: 'periode',
                        name: 'periode',
                        width: '7.5%'
                    },

                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'license_type',
                        name: 'license_type',
                        width: '7.5%'
                    },
                    {
                        data: 'last_login',
                        name: 'last_login',
                        width: '10%'
                    },
                    {
                        data: 'valid_from',
                        name: 'valid_from',
                        render: function(data, type, row, meta) {
                            return `<div style="text-align: center">${data}</div>`;
                        }
                    },
                    {
                        data: 'valid_to',
                        name: 'valid_to',
                        render: function(data, type, row, meta) {
                            return `<div style="text-align: center">${data}</div>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '17.5%'
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

        $('#user_nik_table').on('click', 'button[data-target="#userNIKModal"]', function() {
            let userId = $(this).data('id');

            // Fetch user data and populate the modal
            $.ajax({
                url: `/user-nik/${userId}`,
                type: 'GET',
                success: function(response) {
                    // Populate modal with user data
                    $('#modal-user-nick-details').html(response);
                    // Show the modal
                    $('#userNIKModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to load user details.",
                        icon: "error"
                    });
                }
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
