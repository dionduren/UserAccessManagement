@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">User Generic - Unit Kerja</h1>
                    </div>
                    <div class="card-body">

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

                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-4">
                                <label for="periode_id" class="form-label">Periode</label>
                                <select id="periode_id" class="form-select">
                                    <option value="">-- Pilih Periode --</option>
                                    @foreach ($periodes as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->definisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button id="btn-load" class="btn btn-secondary">Tampilkan</button>
                                <button id="btn-reset" class="btn btn-outline-secondary">Reset</button>
                            </div>
                            <div class="col-md text-end">
                                <a href="{{ route('unit_kerja.user_generic.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus"></i> Buat Relationship User ID Generic - Unit Kerja Baru
                                </a>
                            </div>
                        </div>

                        <table id="grid"
                            class="display table table-bordered table-striped table-hover cell-border mt-3"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th width="7.5%">Perusahaan</th>
                                    <th width="7.5%">User ID</th>
                                    <th>Nama</th>
                                    <th width="20%">Kompartemen</th>
                                    <th width="20%">Departemen</th>
                                    <th width="5%">Flagged</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const table = $('#grid').DataTable({
            ajax: function(data, callback) {
                const pid = $('#periode_id').val();
                if (!pid) {
                    return callback({
                        data: []
                    });
                }
                fetch("{{ route('unit_kerja.user_generic.data') }}?periode_id=" + encodeURIComponent(pid), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(json => callback(json));
            },
            columns: [{
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
                    render: (row) => {
                        const editUrl = "{{ route('unit_kerja.user_generic.edit', ':id') }}".replace(':id',
                            row.id);
                        const delUrl = "{{ route('unit_kerja.user_generic.destroy', ':id') }}".replace(
                            ':id', row.id);

                        let btns = '';
                        btns +=
                            `<a href="${editUrl}" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>`;
                        btns +=
                            `<button type="button" data-id="${row.id}" data-url="${delUrl}" class="btn btn-sm btn-danger btn-delete"><i class="bi bi-trash"></i></button>`;
                        return btns;
                    }
                }
            ]
        });

        $('#btn-load').on('click', () => table.ajax.reload());
        $('#periode_id').on('change', () => table.ajax.reload());
        $('#btn-reset').on('click', () => {
            $('#periode_id').val('');
            table.ajax.reload();
        });

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
