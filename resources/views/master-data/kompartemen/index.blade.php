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

                <h2>Master Data Kompartemen</h2>

            </div>
            <div class="card-body">
                <a href="{{ route('kompartemens.create') }}" class="btn btn-primary mb-3">Buat Info Kompartemen Baru</a>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <!-- Dropdown for Company Selection -->
                <div class="form-group mb-3">
                    <label for="companyDropdown">Pilih Perusahaan</label>
                    <select id="companyDropdown" class="form-control">
                        <option value="">-- Semua Perusahaan --</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <hr class="mt-3 mb-3" style="width: 80%; margin:auto">

                <!-- Table to display all Kompartemen -->
                <table id="kompartemenTable" class="table table-bordered table-striped table-hover cell-border mt-3">
                    <thead>
                        <tr>
                            <th>Nama Perusahaan</th>
                            <th>ID Kompartemen</th>
                            <th>Nama Kompartemen</th>
                            <th>Deskripsi</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kompartemens as $kompartemen)
                            <tr data-company-id="{{ $kompartemen->company_id }}">
                                <td>{{ $kompartemen->company->nama ?? 'N/A' }}</td>
                                <td>{{ $kompartemen->kompartemen_id }}</td>
                                <td>{{ $kompartemen->nama }}</td>
                                <td>{{ $kompartemen->deskripsi }}</td>
                                <td>
                                    <a href="{{ route('kompartemens.edit', $kompartemen) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('kompartemens.destroy', $kompartemen) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <!-- Changed: remove inline confirm and use SweetAlert2 -->
                                        <button type="button" class="btn btn-danger btn-sm btn-delete"
                                            data-name="{{ $kompartemen->nama }}">
                                            Delete
                                        </button>
                                    </form>
                                </td>
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
            let table;

            // SweetAlert2 delete handler (event delegation for dynamic rows)
            $('#kompartemenTable').on('click', '.btn-delete', function(e) {
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

            // Initialize DataTable
            if ($.fn.DataTable) {
                table = $('#kompartemenTable').DataTable({
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    columnDefs: [{
                            width: '20%',
                            targets: [0]
                        },
                        {
                            width: '11%',
                            targets: [1],
                            className: "text-center"
                        },
                        {
                            width: '25%',
                            targets: [2]
                        },
                        {
                            width: '12.5%',
                            orderable: false,
                            targets: [4]
                        }
                    ]
                });
            } else {
                console.error('DataTable library not loaded.');
            }

            // Custom filtering by company
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let selectedCompanyId = $('#companyDropdown').val();
                let rowCompanyId = $(table.row(dataIndex).node()).data('company-id');
                return !selectedCompanyId || rowCompanyId == selectedCompanyId;
            });

            $('#companyDropdown').change(function() {
                table.draw();
            });
        });
    </script>
@endsection
