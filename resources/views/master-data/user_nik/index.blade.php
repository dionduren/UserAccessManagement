@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Dashboard User NIK</h1>

        {{-- <a href="{{ route('user-nik.create') }}" target="_blank" class="btn btn-outline-secondary mb-3"> --}}
        {{-- <a href="{{ route('user-nik.upload.form') }}" class="btn btn-outline-primary mb-3"> --}}
        <a href="{{ route('dynamic_upload.upload', ['module' => 'user_nik']) }}" class="btn btn-primary my-3">
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
                    <th width="10%">Perusahaan</th>
                    <th width="10%">User ID Group</th>
                    <th width="10%">User Detail</th>
                    <th>NIK</th>
                    <th>Tipe Lisensi</th>
                    <th>Login Terakhir</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th>Action</th>
                </tr>
                <!-- Filters row under header -->
                <tr class="filters">
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Perusahaan"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Group"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari User Detail"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari NIK"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Lisensi"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Login"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Valid From"></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Cari Valid To"></th>
                    <th></th> <!-- Action (no filter) -->
                </tr>
            </thead>
        </table>
    </div>

    <div id="modal-user-nick-details"></div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Reload by periode
            $('#periode').on('change', function() {
                const periodeId = $(this).val();
                const dt = $('#user_nik_table').DataTable();
                dt.ajax.url("{{ route('user-nik.index') }}" + (periodeId ? ("?periode=" + periodeId) : ""))
                    .load();
            });

            let userNikTable = $('#user_nik_table').DataTable({
                processing: true,
                serverSide: false,
                orderCellsTop: true, // needed for header filters row
                fixedHeader: true,
                ajax: "{{ route('user-nik.index') }}",
                columns: [{
                        data: 'user_detail_company',
                        name: 'user_detail_company'
                    },
                    {
                        data: 'group',
                        name: 'group'
                    },
                    {
                        data: 'user_detail_exists',
                        name: 'user_detail_exists',
                        render: function(data) {
                            return data ?
                                `<span class="badge bg-success text-white" style="font-size:1em;"><i class="bi bi-check-lg"></i> Exist</span>` :
                                `<span class="badge bg-danger text-white" style="font-size:1em;"><span style="font-weight:bold;">&#10005;</span> Not-Exist</span>`;
                        }
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
                        render: function(data) {
                            return `<div style="text-align:center">${data ?? ''}</div>`;
                        }
                    },
                    {
                        data: 'valid_to',
                        name: 'valid_to',
                        render: function(data) {
                            return `<div style="text-align:center">${data ?? ''}</div>`;
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
                // removed columnDefs that hid ID (no ID column anymore)
                order: [
                    [2, 'asc']
                ],
                initComplete: function() {
                    const api = this.api();
                    // Bind header filter inputs, skip Action (last column)
                    api.columns().every(function(colIdx) {
                        if (colIdx === 8) return; // Action
                        const th = $('#user_nik_table thead tr.filters th').eq(colIdx);
                        const $input = $('input', th);
                        if (!$input.length) return;

                        $input.on('keyup change clear', function() {
                            const val = this.value || '';
                            api.column(colIdx).search(val).draw();
                        });
                    });
                }
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
