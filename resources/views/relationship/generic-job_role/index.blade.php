@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Relationship: User Generic &amp; Job Role</h1>

        <a href="{{ route('user-generic-job-role.create') }}" target="_blank" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-plus"></i> Tambah Relasi User Generic - Job Role
        </a>

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

        <table id="user_generic_jobrole_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th width="10%">Perusahaan</th>
                    <th>User Generic</th>
                    <th>Job Role ID</th>
                    <th>Job Role</th>

                    <th width="10%">Action</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable with no ajax source
            var table = $('#user_generic_jobrole_table').DataTable({
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
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'job_role_id',
                        name: 'job_role_id',
                        createdCell: function(td, cellData) {
                            if (!cellData) $(td).css('background-color', '#f8d7da');
                        }
                    },
                    {
                        data: 'job_role_name',
                        name: 'job_role_name',
                        createdCell: function(td, cellData) {
                            if (!cellData) $(td).css('background-color', '#f8d7da');
                        }
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
                    [1, 'asc']
                ]
            });

            // Only load data when periode is selected
            $('#periode').on('change', function() {
                var periode = $(this).val();
                if (periode) {
                    table.ajax.url("{{ route('user-generic-job-role.index') }}?periode=" + periode).load();
                } else {
                    table.clear().draw(); // Clear table if no periode selected
                }
            });
        });

        // Example delete function (unchanged)
        function deleteRelationship(id) {
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
                        url: '/relationship/user-generic-jobrole/' + id,
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
                            $('#user_generic_jobrole_table').DataTable().ajax.reload();
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
