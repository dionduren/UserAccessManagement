@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">User NIK Without Unit Kerja</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('unit_kerja.user_nik.index') }}" class="btn btn-outline-secondary btn-sm">
                        Back to Unit Kerja
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Alerts --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="periode_id" class="form-label">Periode</label>
                        <select id="periode_id" class="form-select">
                            <option value="">-- Pilih Periode --</option>
                            @foreach ($periodes as $p)
                                <option value="{{ $p->id }}">{{ $p->definisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <table id="user_nik_wo_uk_table" class="table table-bordered table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th>Perusahaan</th>
                            <th>User Code (NIK)</th>
                            <th>Last Login</th>
                            <th>Valid From</th>
                            <th>Valid To</th>
                        </tr>
                        <!-- Filters row under header -->
                        <tr class="filters">
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Perusahaan">
                            </th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari NIK"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Login"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Valid From">
                            </th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Valid To"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            const table = $('#user_nik_wo_uk_table').DataTable({
                processing: true,
                serverSide: false,
                orderCellsTop: true,
                fixedHeader: true,
                ajax: {
                    url: "{{ route('unit_kerja.user_nik.without') }}",
                    data: function(d) {
                        d.periode_id = $('#periode_id').val() || '';
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('Accept', 'application/json');
                    }
                },
                columns: [{
                        data: 'group',
                        name: 'group'
                    },
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'last_login',
                        name: 'last_login'
                    },
                    {
                        data: 'valid_from',
                        name: 'valid_from'
                    },
                    {
                        data: 'valid_to',
                        name: 'valid_to'
                    },
                ],
                initComplete: function() {
                    const api = this.api();
                    api.columns().every(function(colIdx) {
                        const th = $('#user_nik_wo_uk_table thead tr.filters th').eq(colIdx);
                        const $input = $('input', th);
                        if (!$input.length) return;

                        $input.on('keyup change clear', function() {
                            const val = this.value || '';
                            api.column(colIdx).search(val).draw();
                        });
                    });
                }
            });

            $('#periode_id').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
