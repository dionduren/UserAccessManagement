@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">NIK Tanpa Relasi Job Role</h5>
                <div class="d-flex gap-2">
                    <button id="export-excel" class="btn btn-success btn-sm" disabled>
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <a href="{{ route('nik-job.index') }}" class="btn btn-outline-secondary btn-sm">
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
                        <option value="">-- Pilih Periode --</option>
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                        @endforeach
                    </select>
                </div>

                <table id="nik_no_jobrole_table" class="table table-bordered table-striped table-hover cell-border mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Perusahaan</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Kompartemen</th>
                            <th>Departemen</th>
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
        $(document).ready(function() {
            var table = $('#nik_no_jobrole_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: false, // Don't load data initially
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
                        data: 'nama',
                        name: 'user_details.nama'
                    },
                    {
                        data: 'kompartemen',
                        name: 'kompartemen.nama'
                    },
                    {
                        data: 'departemen',
                        name: 'departemen.nama'
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
                    table.ajax.url("{{ route('nik-job.null-relationship') }}?periode=" + periode).load();
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
                const url = new URL("{{ route('nik-job.without.export') }}", window.location.origin);
                url.searchParams.set('periode', periodeId);

                // Trigger download
                window.location.href = url.toString();
            });
        });
    </script>
@endsection
