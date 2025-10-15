@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                        <h4 class="mb-0 flex-grow-1">User Generic - Unit Kerja</h4>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('unit_kerja.user_generic.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Buat Relationship User ID Generic - Unit Kerja Baru
                            </a>
                            <button id="btnBulkDelete" class="btn btn-danger" disabled>Delete Selected</button>
                            <div id="dtButtons" class="btn-group"></div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="periode_id" class="mb-0 small text-nowrap">Periode</label>
                                <select id="periode_id" class="form-select form-select-sm">
                                    <option value="">-- Pilih Periode --</option>
                                    @foreach ($periodes as $p)
                                        <option value="{{ $p->id }}">{{ $p->definisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                <h4>Success:</h4>
                                {{ session('success') }}
                            </div>
                        @endif

                        <table id="grid" class="table table-bordered table-striped table-hover cell-border w-100">
                            <thead>
                                <tr>
                                    <th style="width:32px;">
                                        <input type="checkbox" id="select_all">
                                    </th>
                                    <th width="7.5%">Perusahaan</th>
                                    <th width="7.5%">User ID</th>
                                    <th>Nama</th>
                                    <th width="20%">Kompartemen</th>
                                    <th width="20%">Departemen</th>
                                    <th width="5%">Flagged</th>
                                    <th width="10%">Actions</th>
                                </tr>
                                <tr class="filters">
                                    <th></th>
                                    <th><input data-col="1" type="text" class="form-control form-control-sm"
                                            placeholder="Perusahaan"></th>
                                    <th><input data-col="2" type="text" class="form-control form-control-sm"
                                            placeholder="User ID"></th>
                                    <th><input data-col="3" type="text" class="form-control form-control-sm"
                                            placeholder="Nama"></th>
                                    <th><input data-col="4" type="text" class="form-control form-control-sm"
                                            placeholder="Kompartemen"></th>
                                    <th><input data-col="5" type="text" class="form-control form-control-sm"
                                            placeholder="Departemen"></th>
                                    <th><input data-col="6" type="text" class="form-control form-control-sm"
                                            placeholder="Flagged"></th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('header-scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const selectedIds = new Set();

        const table = $('#grid').DataTable({
            ajax: function(data, callback) {
                const pid = $('#periode_id').val();
                if (!pid) return callback({
                    data: []
                });
                fetch("{{ route('unit_kerja.user_generic.data') }}?periode_id=" + encodeURIComponent(pid), {
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(json => callback(json));
            },
            columns: [{
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const checked = selectedIds.has(String(row.id)) ? 'checked' : '';
                        return `<input type="checkbox" class="row-select" value="${row.id}" ${checked}>`;
                    }
                },
                {
                    data: 'company'
                },
                {
                    data: 'user_cc'
                },
                {
                    data: 'nama'
                },
                {
                    data: 'kompartemen_nama'
                },
                {
                    data: 'departemen_nama'
                },
                {
                    data: 'flagged',
                    render: (d) => d ? 'Yes' : 'No'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const editUrl = "{{ route('unit_kerja.user_generic.edit', ':id') }}".replace(':id',
                            row.id);
                        const delUrl = "{{ route('unit_kerja.user_generic.destroy', ':id') }}".replace(
                            ':id', row.id);
                        return `
                            <a href="${editUrl}" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                            <button type="button" data-id="${row.id}" data-url="${delUrl}" class="btn btn-sm btn-danger btn-delete"><i class="bi bi-trash"></i></button>`;
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
            order: [
                [1, 'asc']
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
                title: 'UserGeneric_UnitKerja',
                filename: 'UserGeneric_UnitKerja_' + new Date().toISOString().slice(0, 10),
                exportOptions: {
                    // Exclude checkbox (first) and actions (last)
                    columns: ':visible:not(:first-child):not(:last-child)'
                }
            }],
            initComplete: function() {
                const api = this.api();
                const wrapper = $(api.table().container());

                // Move built-in controls
                wrapper.find('.dt-length').appendTo(wrapper.find('.pageLength').first());
                wrapper.find('.dt-search').appendTo(wrapper.find('.search').first());
                wrapper.find('.dt-info').appendTo(wrapper.find('.info').first());
                wrapper.find('.dt-paging').appendTo(wrapper.find('.paging').first());

                // Excel button to header
                api.buttons().container().appendTo('#dtButtons');

                // Column filters
                $('#grid thead tr.filters input').on('keyup change', function() {
                    const colIdx = $(this).data('col');
                    const val = this.value;
                    if (api.column(colIdx).search() !== val) {
                        api.column(colIdx).search(val).draw();
                    }
                });
            }
        });

        // Select-all checkbox
        $(document).on('change', '#select_all', function() {
            const checked = this.checked;
            $('#grid tbody input.row-select').each(function() {
                this.checked = checked;
                const id = this.value;
                if (checked) selectedIds.add(String(id));
                else selectedIds.delete(String(id));
            });
            updateBulkDeleteState();
        });

        // Row checkbox toggle (use delegation for redraws)
        $(document).on('change', '#grid tbody input.row-select', function() {
            const id = this.value;
            if (this.checked) selectedIds.add(String(id));
            else selectedIds.delete(String(id));
            syncSelectAll();
            updateBulkDeleteState();
        });

        // Keep header checkbox in sync on draw
        $('#grid').on('draw.dt', function() {
            // Reapply checked state for visible rows
            $('#grid tbody input.row-select').each(function() {
                this.checked = selectedIds.has(String(this.value));
            });
            syncSelectAll();
            updateBulkDeleteState();
        });

        function syncSelectAll() {
            const $rows = $('#grid tbody input.row-select');
            const $checked = $('#grid tbody input.row-select:checked');
            const all = $rows.length > 0 && $rows.length === $checked.length;
            $('#select_all').prop('indeterminate', $checked.length > 0 && !all);
            $('#select_all').prop('checked', all);
        }

        function updateBulkDeleteState() {
            $('#btnBulkDelete').prop('disabled', selectedIds.size === 0);
        }

        // Bulk delete using existing destroy route per id + SweetAlert2
        $('#btnBulkDelete').on('click', async function() {
            if (selectedIds.size === 0) return;

            const result = await Swal.fire({
                title: 'Hapus data terpilih?',
                html: `Anda akan menghapus <b>${selectedIds.size}</b> data.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            });
            if (!result.isConfirmed) return;

            $('#btnBulkDelete').prop('disabled', true);
            Swal.fire({
                title: 'Memproses...',
                html: 'Mohon tunggu, sedang menghapus data.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const ids = Array.from(selectedIds);
            const destroyTpl = @json(route('unit_kerja.user_generic.destroy', ':id'));

            let ok = 0,
                fail = 0;
            for (const id of ids) {
                try {
                    const resp = await fetch(destroyTpl.replace(':id', id), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    ok++;
                } catch (e) {
                    console.error(e);
                    fail++;
                }
            }

            selectedIds.clear();
            $('#select_all').prop('checked', false).prop('indeterminate', false);
            updateBulkDeleteState();
            table.ajax.reload(null, false);

            Swal.close();
            if (fail === 0) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: `${ok} data dihapus.`,
                    timer: 1800,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sebagian gagal',
                    text: `Berhasil: ${ok}, Gagal: ${fail}`
                });
            }
            $('#btnBulkDelete').prop('disabled', selectedIds.size === 0);
        });

        $('#periode_id').on('change', () => {
            selectedIds.clear();
            $('#select_all').prop('checked', false).prop('indeterminate', false);
            updateBulkDeleteState();
            table.ajax.reload();
        });

        // Single delete with SweetAlert2
        $(document).on('click', '.btn-delete', async function() {
            const url = $(this).data('url');
            const id = String($(this).data('id'));

            const result = await Swal.fire({
                title: 'Hapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            });
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);

                selectedIds.delete(id);
                table.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus',
                    text: 'Data berhasil dihapus.',
                    timer: 1500,
                    showConfirmButton: false
                });
                updateBulkDeleteState();
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menghapus data.'
                });
            }
        });
    </script>
@endsection
