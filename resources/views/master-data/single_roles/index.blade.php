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

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Master Data Single Roles</h2>
            </div>
            <div class="card-body">
                @if (isset($userCompanyCode) && $userCompanyCode == 'A000')
                    <button type="button" id="triggerCreateModal" class="btn btn-primary mb-3">
                        Buat Single Role Baru
                    </button>
                @endif

                <style>
                    /* remove tfoot hack since we use header filters now */
                </style>

                <table id="single_roles_table" class="table table-bordered table-striped table-hover cell-border mt-3"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>Single Role</th>
                            <th>Deskripsi</th>
                            <th width="10%">Sumber</th>
                            <th>Actions</th>
                        </tr>
                        <tr>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Single Role">
                            </th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Deskripsi">
                            </th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Sumber"></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade" id="singleRoleModal" tabindex="-1" aria-labelledby="singleRoleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="singleRoleModalLabel"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="singleRoleModalBody"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#single_roles_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/single-roles/data'
                },
                columns: [{
                        data: 'nama',
                        name: 'nama',
                        title: 'Single Role'
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi',
                        title: 'Deskripsi',
                        width: '50%'
                    },
                    {
                        data: 'source',
                        name: 'source',
                        title: 'Sumber',
                        render: function(data, type) {
                            if (type === 'display' || type === 'filter') {
                                if (data === 'import') return 'MDB';
                                if (data === 'manual' || data === 'edit') return 'SYS';
                                if (data === 'upload') return 'UPL';
                                return data ?? '';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        title: 'Actions',
                        width: '10%'
                    },
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [
                    [0, 'asc']
                ],
                orderCellsTop: true,
                initComplete: function() {
                    const api = this.api();
                    // Bind to inputs in the second header row
                    $('#single_roles_table thead tr:eq(1) th').each(function(i) {
                        const $input = $(this).find('input');
                        if ($input.length) {
                            $input.on('keyup change clear', function() {
                                const val = this.value;
                                if (api.column(i).search() !== val) {
                                    api.column(i).search(val).draw();
                                }
                            });
                        }
                    });
                }
            });

            function loadModalContent(url, title) {
                $('#singleRoleModalLabel').text(title);
                $('#singleRoleModalBody').html('<div class="text-center">Loading...</div>');
                $('#singleRoleModal').modal('show');
                $.get(url, function(data) {
                    $('#singleRoleModalBody').html(data);
                }).fail(function() {
                    alert('Failed to load data. Please try again.');
                });
            }

            $('#triggerCreateModal').on('click', function() {
                loadModalContent('{{ route('single-roles.create') }}', 'Create Single Role');
            });

            $(document).on('click', '.edit-single-role', function() {
                const roleId = $(this).data('id');
                loadModalContent(`/single-roles/${roleId}/edit`, 'Edit Single Role');
            });

            $(document).on('click', '.show-single-role', function() {
                const roleId = $(this).data('id');
                loadModalContent(`/single-roles/${roleId}`, 'Single Role Details');
            });

            $(document).on('click', '.close', function() {
                $('#singleRoleModal').modal('hide');
            });

            // Create/Edit submit with SweetAlert2 guidance (opens target in new tab)
            $(document).on('submit', 'form.ajax-modal-form', function(event) {
                event.preventDefault();
                const form = $(this);
                const actionUrl = form.attr('action');
                const method = form.attr('method') || 'POST';
                const formData = form.serialize();

                $.ajax({
                    url: actionUrl,
                    method: method,
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false);
                            $('#singleRoleModal').modal('hide');
                        } else {
                            Swal.fire('Gagal', 'Tidak dapat menyimpan perubahan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        const meta = xhr.responseJSON?.meta || {};
                        const links = meta.links || {};
                        let singleTcodeEditUrl = links.single_tcode_edit || null;
                        let compositeSingleUrl = links.composite_single_index ||
                            `{{ route('composite-single.index') }}`;

                        if (!singleTcodeEditUrl) {
                            let roleId = null;
                            try {
                                const match = (actionUrl || '').match(/single-roles\/(\d+)/);
                                roleId = match ? match[1] : null;
                            } catch (e) {}
                            singleTcodeEditUrl = roleId ? `/single-tcode/${roleId}/edit` :
                                `{{ route('single-tcode.index') }}`;
                        }

                        if (xhr.status === 422 && xhr.responseJSON?.errors?.nama) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Nama Single Role sudah digunakan',
                                html: 'Silahkan mengubah konfigurasi mapping Composite Single Role dan Single Role - Tcode role ini pada masing-masing menu.',
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonText: 'Kelola Single Role - Tcode',
                                denyButtonText: 'Kelola Composite - Single Role',
                                cancelButtonText: 'Tutup'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open(singleTcodeEditUrl, '_blank');
                                } else if (result.isDenied) {
                                    window.open(compositeSingleUrl, '_blank');
                                }
                            });
                        } else {
                            const msg = xhr.responseJSON?.message ||
                                'Terjadi kesalahan. Coba lagi.';
                            Swal.fire('Error', msg, 'error');
                        }
                    }
                });
            });

            $(document).on('click', '.delete-single-role', function(e) {
                e.preventDefault();
                const url = $(this).data('url');

                Swal.fire({
                    title: 'Menghapus Data Ini?',
                    text: "Anda akan menghapus data ini secara permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form>', {
                            method: 'POST',
                            action: url
                        });
                        const token = $('meta[name="csrf-token"]').attr('content');
                        form.append($('<input>', {
                            type: 'hidden',
                            name: '_token',
                            value: token
                        }));
                        form.append($('<input>', {
                            type: 'hidden',
                            name: '_method',
                            value: 'DELETE'
                        }));
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
