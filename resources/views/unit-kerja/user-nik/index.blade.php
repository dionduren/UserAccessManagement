@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h4 class="mb-0 flex-grow-1">User NIK - Unit Kerja</h4>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('unit_kerja.user_nik.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Buat Relationship NIK - Unit Kerja Baru
                    </a>
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

                <div class="table-responsive">
                    <table id="grid" class="table table-bordered table-striped table-hover cell-border w-100">
                        <thead>
                            <tr>
                                <th width="5%">Company</th>
                                <th width="10%">NIK</th>
                                <th>Nama</th>
                                <th width="25%">Kompartemen</th>
                                <th width="25%">Departemen</th>
                                <th width="7.5%">Flagged</th>
                                <th width="10%">Actions</th>
                            </tr>
                            <tr class="filters">
                                <th><input data-col="0" type="text" class="form-control form-control-sm"
                                        placeholder="Company"></th>
                                <th><input data-col="1" type="text" class="form-control form-control-sm"
                                        placeholder="NIK"></th>
                                <th><input data-col="2" type="text" class="form-control form-control-sm"
                                        placeholder="Nama"></th>
                                <th><input data-col="3" type="text" class="form-control form-control-sm"
                                        placeholder="Kompartemen"></th>
                                <th><input data-col="4" type="text" class="form-control form-control-sm"
                                        placeholder="Departemen"></th>
                                <th><input data-col="5" type="text" class="form-control form-control-sm"
                                        placeholder="Flagged"></th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('header-scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const table = $('#grid').DataTable({
            ajax: function(data, callback) {
                const pid = $('#periode_id').val();
                if (!pid) return callback({
                    data: []
                });
                fetch("{{ route('unit_kerja.user_nik.data') }}?periode_id=" + encodeURIComponent(pid), {
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(json => callback(json));
            },
            columns: [{
                    data: 'company_id'
                },
                {
                    data: 'nik'
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
                    render: d => d ? 'Yes' : 'No'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: (row) => {
                        const editUrl = "{{ route('unit_kerja.user_nik.edit', ':id') }}".replace(':id', row
                            .id);
                        const delUrl = "{{ route('unit_kerja.user_nik.destroy', ':id') }}".replace(':id',
                            row.id);
                        return `
                            <a href="${editUrl}" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                            <button type="button" data-id="${row.id}" data-url="${delUrl}" class="btn btn-sm btn-danger btn-delete">
                                <i class="bi bi-trash"></i>
                            </button>`;
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
                title: 'UserNIK_UnitKerja',
                filename: 'UserNIK_UnitKerja_' + new Date().toISOString().slice(0, 10),
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            }],
            initComplete: function() {
                const api = this.api();
                const wrapper = $(api.table().container());

                // Move built-in controls into layout slots
                wrapper.find('.dt-length').appendTo(wrapper.find('.pageLength').first());
                wrapper.find('.dt-search').appendTo(wrapper.find('.search').first());
                wrapper.find('.dt-info').appendTo(wrapper.find('.info').first());
                wrapper.find('.dt-paging').appendTo(wrapper.find('.paging').first());

                // Put Excel button in card header
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

        $('#periode_id').on('change', () => table.ajax.reload());

        $(document).on('click', '.btn-delete', function() {
            const url = $(this).data('url');
            if (!confirm('Soft delete this item?')) return;
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            }).then(() => table.ajax.reload(null, false));
        });
    </script>
@endsection
