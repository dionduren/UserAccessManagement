@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">User Generic Tanpa Relasi Job Role</h5>
                <div class="d-flex gap-2">
                    <button id="export-excel" class="btn btn-success btn-sm" disabled>
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <a href="{{ route('user-generic-job-role.index') }}" class="btn btn-outline-secondary btn-sm">
                        Kembali ke Relasi
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

                <div class="form-group mb-3">
                    <label for="periode">Periode</label>
                    <select name="periode" id="periode" class="form-control">
                        <option value="">Silahkan Pilih Periode Data</option>
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                        @endforeach
                    </select>
                </div>

                <table id="user_generic_no_jobrole_table"
                    class="table table-bordered table-striped table-hover cell-border">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Perusahaan</th>
                            <th>User Code</th>
                            <th>Kompartemen</th>
                            <th>Departemen</th>
                            <th>Last Login</th>
                            {{-- <th>Wrong Job Role ID</th> --}}
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            var table = $('#user_generic_no_jobrole_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: false,
                columns: [{
                        data: 'id',
                        name: 'id',
                        visible: false
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
                        data: 'kompartemen',
                        name: 'kompartemen'
                    },
                    {
                        data: 'departemen',
                        name: 'departemen'
                    },
                    {
                        data: 'last_login',
                        name: 'last_login',
                        render: function(data, type, row) {
                            if (!data) return '';
                            var date = new Date(data);
                            if (type === 'sort' || type === 'type') {
                                // Return sortable format: YYYYMMDD
                                var y = date.getFullYear();
                                var m = String(date.getMonth() + 1).padStart(2, '0');
                                var d = String(date.getDate()).padStart(2, '0');
                                return y + m + d;
                            }
                            // Display format: DD Month YYYY
                            var day = String(date.getDate()).padStart(2, '0');
                            var month = date.toLocaleString('id-ID', {
                                month: 'long'
                            });
                            var year = date.getFullYear();
                            return day + ' ' + month + ' ' + year;
                        }
                    },
                    // {
                    //     data: 'wrong_job_role_id',
                    //     name: 'wrong_job_role_id',
                    //     defaultContent: ''
                    // }
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });

            $('#periode').on('change', function() {
                var periode = $(this).val();
                if (periode) {
                    table.ajax.url("{{ route('user-generic-job-role.null-relationship') }}?periode=" +
                        periode).load();
                    $('#export-excel').prop('disabled', false);
                } else {
                    table.clear().draw();
                    $('#export-excel').prop('disabled', true);
                }
            });

            // Export button click handler
            $('#export-excel').on('click', function(e) {
                e.preventDefault();
                const periodeId = $('#periode').val();

                if (!periodeId) {
                    alert('Silakan pilih periode terlebih dahulu');
                    return;
                }

                // Create download URL with parameters
                const url = new URL("{{ route('user-generic-job-role.without.export') }}", window.location
                    .origin);
                url.searchParams.set('periode', periodeId);

                // Trigger download
                window.location.href = url.toString();
            });
        });
    </script>
@endsection
