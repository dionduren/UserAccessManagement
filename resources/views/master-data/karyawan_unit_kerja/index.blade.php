@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">Unit Kerja - Karyawan</h1>
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

                        @if (session('success'))
                            <div class="alert alert-success py-2">{{ session('success') }}</div>
                        @endif

                        <div class="mb-3">
                            <a href="{{ route('karyawan_unit_kerja.create') }}" class="btn btn-primary">Create</a>
                        </div>

                        <div class="card">
                            <div class="card-body table-responsive">
                                <table id="karyawanTable" class="table table-bordered table-striped table-sm"
                                    style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Company</th>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Direktorat</th>
                                            <th>Kompartemen ID</th>
                                            <th>Kompartemen</th>
                                            <th>Departemen ID</th>
                                            <th>Departemen</th>
                                            <th>Cost Center</th>
                                            <th>Atasan</th>
                                            <th style="width:100px">Action</th>
                                        </tr>
                                    </thead>
                                </table>
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
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#karyawanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('karyawan_unit_kerja.data') }}',
                columns: [{
                        data: 'company',
                        name: 'company'
                    }, {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'direktorat',
                        name: 'direktorat'
                    },
                    {
                        data: 'kompartemen_id',
                        name: 'kompartemen_id'
                    },
                    {
                        data: 'kompartemen',
                        name: 'kompartemen'
                    },
                    {
                        data: 'departemen_id',
                        name: 'departemen_id'
                    },
                    {
                        data: 'departemen',
                        name: 'departemen'
                    },
                    {
                        data: 'cost_center',
                        name: 'cost_center'
                    },
                    {
                        data: 'atasan',
                        name: 'atasan'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ]
            });

            $('#karyawanTable').on('click', '.btn-delete', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Delete?',
                    text: 'This record will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then(res => {
                    if (!res.isConfirmed) return;
                    fetch('{{ url('master-karyawan-local') }}/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (j.status === 'ok') {
                                Swal.fire('Deleted', 'Record removed', 'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', 'Failed', 'error');
                            }
                        }).catch(() => Swal.fire('Error', 'Failed', 'error'));
                });
            });
        });
    </script>
@endsection
