@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">Unit Kerja Sync</h1>
                    </div>
                    <div class="card-body">

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session('validationErrors'))
                            <div class="alert alert-danger">
                                <h4>Validation Errors:</h4>
                                <ul>
                                    @foreach (session('validationErrors') as $row => $messages)
                                        <li><strong>Row {{ $row }}:</strong>
                                            <ul>
                                                @foreach ($messages as $message)
                                                    @if (is_array($message))
                                                        @foreach ($message as $subMessage)
                                                            <li>{{ $subMessage }}</li>
                                                        @endforeach
                                                    @else
                                                        <li>{{ $message }}</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <strong>Information</strong>
                            </div>
                            <div class="card-body small">
                                <ul class="mb-2">
                                    <li>Menyinkronkan data organisasi (Direktorat → Kompartemen → Departemen) dari tabel
                                        middle DB
                                        <code>mdb_unit_kerja</code>.
                                    </li>
                                    <li>Tabel Kompartemen / Departemen / Cost Center yang ada akan di-TRUNCATE (data
                                        perusahaan tetap
                                        dipertahankan).</li>
                                    <li>Opsional: refresh tabel middle dari sumber eksternal sebelum dibangun ulang.</li>
                                </ul>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="refreshToggle" checked>
                                    <label class="form-check-label" for="refreshToggle">
                                        Perbaharui data middle db sebelum melakukan sync
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <button id="btnSync" class="btn btn-primary">
                                        <i class="bi bi-arrow-repeat me-1"></i> Import Data Organisasi
                                    </button>
                                    <button id="btnKaryawanSync" class="btn btn-success">
                                        <i class="bi bi-people-fill me-1"></i> Import Mapping Unit Kerja - Karyawan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="resultCard" class="card d-none">
                            <div class="card-header">
                                <strong>Last Sync Result</strong>
                            </div>
                            <div class="card-body">
                                <pre id="resultJson" class="mb-0" style="white-space:pre-wrap;font-size:12px;"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('btnSync').addEventListener('click', function() {
            const refresh = document.getElementById('refreshToggle').checked ? 1 : 0;

            Swal.fire({
                title: 'Confirm Sync',
                html: 'This will <b>TRUNCATE</b> Kompartemen, Departemen, and Cost Center tables and rebuild them.<br>Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, sync now',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Sync in progress...',
                    html: 'Please wait while data is rebuilt.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("{{ route('import.unit_kerja.sync') }}?refresh=" + refresh, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json().then(j => ({
                        ok: r.ok,
                        data: j
                    })))
                    .then(({
                        ok,
                        data
                    }) => {
                        if (ok) {
                            Swal.fire({
                                title: 'Sync Completed',
                                icon: 'success',
                                html: 'Hierarchy rebuilt successfully.'
                            });
                            const pre = document.getElementById('resultJson');
                            pre.textContent = JSON.stringify(data, null, 2);
                            document.getElementById('resultCard').classList.remove('d-none');
                        } else {
                            throw data;
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            title: 'Sync Failed',
                            icon: 'error',
                            html: (err && err.error) ? err.error : 'Unexpected error.'
                        });
                        console.error(err);
                    });
            });
        });

        document.getElementById('btnKaryawanSync').addEventListener('click', function() {
            const refresh = document.getElementById('refreshToggle').checked ? 1 : 0;

            Swal.fire({
                title: 'Confirm Karyawan Sync',
                html: 'This will truncate local master karyawan table and reload from middle DB.<br>Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, sync',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Karyawan Sync in progress...',
                    html: 'Please wait.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch("{{ route('import.unit_kerja.karyawan.sync') }}?refresh=" + refresh, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json().then(j => ({
                        ok: r.ok,
                        data: j
                    })))
                    .then(({
                        ok,
                        data
                    }) => {
                        if (ok) {
                            Swal.fire({
                                title: 'Karyawan Sync Completed',
                                icon: 'success',
                                html: 'Employee master data refreshed.'
                            });
                            const pre = document.getElementById('resultJson');
                            pre.textContent = JSON.stringify(data, null, 2);
                            document.getElementById('resultCard').classList.remove('d-none');
                        } else {
                            throw data;
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            title: 'Karyawan Sync Failed',
                            icon: 'error',
                            html: (err && err.error) ? err.error : 'Unexpected error.'
                        });
                        console.error(err);
                    });
            });
        });
    </script>
@endsection
