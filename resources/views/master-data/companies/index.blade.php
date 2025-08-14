@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- General Error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Master Data Perusahaan</h4>
            </div>
            <div class="card-body">

                @can('Super Admin')
                    <div class="d-flex justify-content-start mb-3">
                        <a href="{{ route('companies.create') }}" class="btn btn-primary mr-3">Buat Info Perusahaan Baru</a>
                        <a href="{{ route('json.regenerate') }}" class="btn btn-secondary ms-3">Regenerate JSON File</a>
                    </div>
                @endcan

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <table id="companiesTable" class="table table-bordered table-striped table-hover table- cell-border hover">
                    <thead>
                        <tr>
                            <th>Company Code</th>
                            <th>Nama Perusahaan</th>
                            <th>Singkatan</th>
                            <th>Deskripsi</th>
                            @can('Super Admin')
                                <th class="no-sort">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companies as $company)
                            <tr>
                                <td>{{ $company->company_code }}</td>
                                <td>{{ $company->nama }}</td>
                                <td>{{ $company->shortname }}</td>
                                <td>{{ $company->deskripsi }}</td>
                                @can('Super Admin')
                                    <td>
                                        <a href="{{ route('companies.edit', $company) }}"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('companies.destroy', $company) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                data-name="{{ $company->nama }}">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // SweetAlert2 delete handler (event delegation)
            $('#companiesTable').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const name = $(this).data('name') || 'item';

                Swal.fire({
                    title: 'Hapus data?',
                    text: `Anda akan menghapus "${name}". Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            if ($.fn.DataTable) {
                const columnDefs = [{
                        width: '12.5%',
                        targets: 0
                    },
                    {
                        width: '25%',
                        targets: 1
                    },
                    {
                        width: '10%',
                        targets: 2
                    },
                    {
                        orderable: false,
                        width: '12.5%',
                        targets: 'no-sort'
                    } // keep Actions unsortable
                ];

                $('#companiesTable').DataTable({
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    columnDefs
                });
            } else {
                console.error('DataTable library not loaded.');
            }
        });
    </script>
@endsection
