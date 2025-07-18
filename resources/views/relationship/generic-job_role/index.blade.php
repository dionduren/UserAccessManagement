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
                    <th width="15%">User Generic</th>
                    <th width="15%">PIC</th>
                    <th width="15%">Job Role ID</th>
                    <th>Job Role</th>
                    <th width="10%">Status</th>
                    <th width="15%">Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Detail/Edit Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="flaggedForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModalLabel">Detail User Generic - Job Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="modal-id" name="id">
                        <table class="table table-bordered">
                            <tr>
                                <th>User Generic</th>
                                <td id="modal-user-generic"></td>
                            </tr>
                            <tr>
                                <th>Job Role ID</th>
                                <td id="modal-job-role-id"></td>
                            </tr>
                            <tr>
                                <th>Job Role Name</th>
                                <td id="modal-job-role-name"></td>
                            </tr>
                            <tr>
                                <th>Kompartemen</th>
                                <td id="modal-kompartemen"></td>
                            </tr>
                            <tr>
                                <th>Departemen</th>
                                <td id="modal-departemen"></td>
                            </tr>
                            <tr>
                                <th>Flagged</th>
                                <td>
                                    <span id="modal-flagged-view"></span>
                                    <select id="modal-flagged-edit" name="flagged" class="form-select d-none">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Keterangan Flagged</th>
                                <td>
                                    <span id="modal-keterangan-flagged-view"></span>
                                    <textarea id="modal-keterangan-flagged-edit" name="keterangan_flagged" class="form-control d-none"></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" id="edit-flagged-btn">Edit Flagged</button>
                        <button type="submit" class="btn btn-success d-none" id="save-flagged-btn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable with no ajax source
            var table = $('#user_generic_jobrole_table').DataTable({
                processing: true,
                serverSide: false,
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
                        data: 'definisi',
                        name: 'definisi'
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
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            // return `
                        //     <button class="btn btn-info btn-sm show-detail" data-id="${row.id}">
                        //         <i class="bi bi-eye"></i> Detail
                        //     </button>
                        //     <a href="/relationship/generic-job-role/${row.id}/edit" class="btn btn-sm btn-outline-warning">
                        //         <i class="fas fa-edit"></i> Edit
                        //     </a>
                        //     <button onclick="deleteRelationship(${row.id})" class="btn btn-sm btn-outline-danger">
                        //         <i class="fas fa-trash"></i> Delete
                        //     </button>
                        // `;
                            return `
                                <button class="btn btn-info btn-sm show-detail" data-id="${row.id}">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                            `;
                        }
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
                    [6, 'desc']
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
                        url: '/relationship/generic-job-role/' + id,
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

        // Show detail (view mode)
        $(document).on('click', '.show-detail', function() {
            var id = $(this).data('id');
            $.ajax({
                url: '/relationship/generic-job-role/' + id,
                method: 'GET',
                success: function(response) {
                    $('#modal-id').val(id);
                    $('#modal-user-generic').text(response.user_code || '-');
                    $('#modal-job-role-id').text(response.job_role_id || '-');
                    $('#modal-job-role-name').text(response.job_role_name || '-');
                    $('#modal-kompartemen').text(response.kompartemen_nama || response.kompartemen_id ||
                        '-');
                    $('#modal-departemen').text(response.departemen_nama || response.departemen_id ||
                        '-');
                    $('#modal-flagged-view').html(response.flagged ?
                        '<span class="badge bg-danger">Yes</span>' :
                        '<span class="badge bg-success">No</span>');
                    $('#modal-flagged-edit').val(response.flagged ? 1 : 0);
                    $('#modal-keterangan-flagged-view').html(response.keterangan_flagged ? response
                        .keterangan_flagged.replace(/\n/g, '<br>') : '-');
                    $('#modal-keterangan-flagged-edit').val(response.keterangan_flagged || '');

                    // Show view, hide edit
                    $('#modal-flagged-view, #modal-keterangan-flagged-view').removeClass('d-none');
                    $('#modal-flagged-edit, #modal-keterangan-flagged-edit, #save-flagged-btn')
                        .addClass('d-none');
                    $('#edit-flagged-btn').removeClass('d-none');

                    var modal = new bootstrap.Modal(document.getElementById('detailModal'));
                    modal.show();
                },
                error: function(xhr) {
                    Swal.fire({
                        title: "Error!",
                        text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                            .message : "Failed to fetch detail data.",
                        icon: "error"
                    });
                }
            });
        });

        // Switch to edit mode
        $('#edit-flagged-btn').on('click', function() {
            $('#modal-flagged-view, #modal-keterangan-flagged-view').addClass('d-none');
            $('#modal-flagged-edit, #modal-keterangan-flagged-edit, #save-flagged-btn').removeClass('d-none');
            $(this).addClass('d-none');
        });

        // Save flagged/keterangan_flagged
        $('#flaggedForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#modal-id').val();
            $.ajax({
                url: '/relationship/generic-job-role/' + id + '/flagged',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    flagged: $('#modal-flagged-edit').val(),
                    keterangan_flagged: $('#modal-keterangan-flagged-edit').val()
                },
                success: function(response) {
                    // Optionally reload table or update row
                    $('#detailModal').modal('hide');
                    location.reload();
                },
                error: function() {
                    alert('Failed to update flagged status.');
                }
            });
        });
    </script>
@endsection
