@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Master Data Periode</h1>

        @if ($user_company === 'A000')
            <a href="{{ route('periode.create') }}" target="_blank" class="btn btn-outline-primary mb-3">
                <i class="bi bi-plus"></i> Buat Periode Baru
            </a>
        @endif

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {!! clean(session('success')) !!}
            </div>
        @endif

        <table id="periode_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Definisi</th>
                    <th>Tanggal Pembuatan</th>
                    @if ($user_company === 'A000')
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deletePeriode(id, definisi) {
            Swal.fire({
                title: 'Hapus Periode?',
                html: 'Periode: <strong>' + definisi +
                    '</strong><br>Akan menghapus SEMUA data dengan periode ini (User Generic, User NIK, Mapping, Job Role).',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch("{{ url('/periode') }}/" + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json().then(j => ({
                        ok: r.ok,
                        data: j
                    })))
                    .then(res => {
                        if (!res.ok && res.data.need_force) {
                            // Periode aktif, minta konfirmasi force
                            Swal.fire({
                                title: 'Periode Aktif',
                                html: 'Periode ini masih aktif.<br>Lanjut hapus pakai FORCE?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Force Delete',
                                cancelButtonText: 'Batal',
                            }).then(f2 => {
                                if (!f2.isConfirmed) return;
                                forceDelete(id, definisi);
                            });
                            return;
                        }

                        if (!res.ok) {
                            Swal.fire('Gagal', res.data.message || 'Gagal hapus', 'error');
                            return;
                        }

                        showResult(res.data);
                    })
                    .catch(e => Swal.fire('Error', e.message, 'error'));
            });
        }

        function forceDelete(id, definisi) {
            fetch("{{ url('/periode') }}/" + id + "?force=1", {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json().then(j => ({
                    ok: r.ok,
                    data: j
                })))
                .then(res => {
                    if (!res.ok) {
                        Swal.fire('Gagal', res.data.message || 'Force delete gagal', 'error');
                        return;
                    }
                    showResult(res.data);
                })
                .catch(e => Swal.fire('Error', e.message, 'error'));
        }

        function showResult(payload) {
            const s = payload.summary || {};
            Swal.fire({
                title: 'Berhasil',
                icon: 'success',
                html: `
                    <div class="text-start small">
                        <div><strong>Periode:</strong> ${s.periode_definisi} (ID ${s.periode_id})</div>
                        <hr class="my-1">
                        <ul class="mb-0">
                            <li>User Generic: <code>${s.deleted?.user_generic ?? 0}</code></li>
                            <li>User System: <code>${s.deleted?.user_generic_system ?? 0}</code></li>
                            <li>User NIK: <code>${s.deleted?.user_nik ?? 0}</code></li>
                            <li>Mapping Generic Unit Kerja: <code>${s.deleted?.user_generic_unit_kerja ?? 0}</code></li>
                            <li>Mapping NIK Unit Kerja: <code>${s.deleted?.user_nik_unit_kerja ?? 0}</code></li>
                            <li>NIK Job Role: <code>${s.deleted?.nik_job_role ?? 0}</code></li>
                        </ul>
                    </div>
                `
            }).then(() => {
                $('#periode_table').DataTable().ajax.reload(null, false);
            });
        }

        $(document).ready(function() {
            const showAction = @json($user_company === 'A000');

            let columns = [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'definisi',
                    name: 'definisi'
                },
                {
                    data: 'tanggal_create_periode',
                    name: 'tanggal_create_periode'
                },
            ];

            if (showAction) {
                columns.push({
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                });
            }

            $('#periode_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('periode.index') }}",
                columns: columns,
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
                    [2, 'asc']
                ]
            });
        });
    </script>
@endsection
