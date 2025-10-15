@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <h4>Error:</h4>
                {{ session('error') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h4 class="mb-0 flex-grow-1">Relationship: User Generic &amp; Job Role</h4>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('user-generic-job-role.create') }}" target="_blank" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Tambah Relasi User Generic - Job Role
                    </a>
                    <div id="dtButtons" class="btn-group"></div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="periode" class="mb-0 small text-nowrap">Periode</label>
                        <select name="periode" id="periode" class="form-select form-select-sm">
                            <option value="">Silahkan Pilih Periode Data</option>
                            @foreach ($periodes as $periode)
                                <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table id="user_generic_jobrole_table"
                    class="table table-bordered table-striped table-hover cell-border w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th width="10%">Perusahaan</th>
                            <th width="15%">User Generic</th>
                            <th width="15%">PIC</th>
                            <th width="15%">Job Role ID</th>
                            <th>Job Role</th>
                            <th width="10%">Status</th>
                            <th width="17.5%">Action</th>
                        </tr>
                        <tr class="filters">
                            <th></th>
                            <th><input data-col="1" type="text" class="form-control form-control-sm"
                                    placeholder="Perusahaan"></th>
                            <th><input data-col="2" type="text" class="form-control form-control-sm"
                                    placeholder="User Generic"></th>
                            <th><input data-col="3" type="text" class="form-control form-control-sm" placeholder="PIC">
                            </th>
                            <th><input data-col="4" type="text" class="form-control form-control-sm"
                                    placeholder="Job Role ID"></th>
                            <th><input data-col="5" type="text" class="form-control form-control-sm"
                                    placeholder="Job Role"></th>
                            <th><input data-col="6" type="text" class="form-control form-control-sm"
                                    placeholder="Status"></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
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

@section('header-scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endsection

@section('scripts')
    <!-- Export deps -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            const table = $('#user_generic_jobrole_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('user-generic-job-role.index') }}",
                    data: function(d) {
                        d.periode = $('#periode').val() || '';
                    },
                    dataSrc: function(json) {
                        return json && json.data ? json.data : [];
                    }
                },
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
                            if (data === 'true' || data === true || data == 1) {
                                var tooltip = row.keterangan_flagged ? row.keterangan_flagged
                                    .replace(/"/g, '&quot;') : '';
                                return `<span class="badge bg-danger">Terdapat Error Data <i class="bi bi-info-circle" title="${tooltip}"></i></span>`;
                            } else if (data === 'false' || data === false || data == 0) {
                                if (!row.job_role_id) {
                                    return '<span class="badge bg-warning text-dark">Job Role Belum Diisi</span>';
                                }
                                return '<span class="badge bg-success">Data Valid</span>';
                            } else {
                                return '<span class="badge bg-secondary">Unknown</span>';
                            }
                        },
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-info btn-sm show-detail" data-id="${row.id}">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <a href="/relationship/generic-job-role/${row.id}/edit" target="_blank" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <button onclick="deleteRelationship(${row.id})" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Delete
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
                lengthMenu: [
                    [5, 10, 25, 50, 100, -1],
                    [5, 10, 25, 50, 100, 'All']
                ],
                columnDefs: [{
                    targets: [0],
                    visible: false
                }],
                order: [
                    [6, 'desc']
                ],
                layout: {
                    top1Start: {
                        div: {
                            className: 'pageLength'
                        }
                    },
                    top1End: {
                        div: {
                            className: 'search'
                        }
                    },
                    bottom1Start: {
                        div: {
                            className: 'info'
                        }
                    },
                    bottom1End: {
                        div: {
                            className: 'paging'
                        }
                    }
                },
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'UserGeneric_JobRole',
                    filename: 'UserGeneric_JobRole_' + new Date().toISOString().slice(0, 10),
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                }],
                initComplete: function() {
                    const api = this.api();
                    const wrapper = $(api.table().container());

                    // Place built-in controls into their layout slots (inside card body)
                    wrapper.find('.dt-length').appendTo(wrapper.find('.pageLength').first());
                    wrapper.find('.dt-search').appendTo(wrapper.find('.search').first());
                    wrapper.find('.dt-info').appendTo(wrapper.find('.info').first());
                    wrapper.find('.dt-paging').appendTo(wrapper.find('.paging').first());

                    // Move Excel button to card header group
                    api.buttons().container().appendTo('#dtButtons');

                    // Column filters
                    $('#user_generic_jobrole_table thead tr.filters input').on('keyup change',
                        function() {
                            const colIdx = $(this).data('col');
                            const val = this.value;
                            if (api.column(colIdx).search() !== val) {
                                api.column(colIdx).search(val).draw();
                            }
                        });
                }
            });

            // Reload on periode change
            $('#periode').on('change', function() {
                table.ajax.reload();
            });
        });

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
                        error: function() {
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

                    $('#modal-flagged-view, #modal-keterangan-flagged-view').removeClass('d-none');
                    $('#modal-flagged-edit, #modal-keterangan-flagged-edit, #save-flagged-btn')
                        .addClass('d-none');
                    $('#edit-flagged-btn').removeClass('d-none');

                    new bootstrap.Modal(document.getElementById('detailModal')).show();
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

        $('#edit-flagged-btn').on('click', function() {
            $('#modal-flagged-view, #modal-keterangan-flagged-view').addClass('d-none');
            $('#modal-flagged-edit, #modal-keterangan-flagged-edit, #save-flagged-btn').removeClass('d-none');
            $(this).addClass('d-none');
        });

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
                success: function() {
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
