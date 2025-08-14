@extends('layouts.app')

@section('content')
    <div class="container-fluid">
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
                <h2>Master Data Departemen</h2>
            </div>
            <div class="card-body">

                <a href="{{ route('departemens.create') }}" class="btn btn-primary mb-3">Buat Info Departemen Baru</a>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="form-group mb-3">
                    <label for="companyDropdown">Pilih Perusahaan</label>
                    <select id="companyDropdown" class="form-control">
                        <option value="">-- Semua Perusahaan --</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="kompartemenDropdown">Pilih Kompartemen</label>
                    <select id="kompartemenDropdown" class="form-control" disabled>
                        <option value="">-- Semua Kompartemen --</option>
                    </select>
                </div>

                <div id="preSelectNotice" class="alert alert-info mb-0">
                    Silakan pilih perusahaan terlebih dahulu untuk menampilkan data Departemen.
                </div>

                <hr class="mt-3 mb-3" style="width: 80%; margin:auto">

                <!-- Wrapper hidden until company selected -->
                <div id="departemenTableWrapper" class="d-none">
                    <table id="departemenTable" class="table table-bordered table-hover cell-border mt-3 w-100">
                        <thead>
                            <tr>
                                <th>Perusahaan</th>
                                <th>Kompartemen</th>
                                <th>ID Departemen</th>
                                <th>Nama Departemen</th>
                                <th>Deskripsi</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departemens as $departemen)
                                <tr data-company-id="{{ $departemen->company_id }}"
                                    data-kompartemen-id="{{ $departemen->kompartemen_id ?? '' }}">
                                    <td>{{ $departemen->company->nama ?? null }}</td>
                                    <td>{{ $departemen->kompartemen->nama ?? null }}</td>
                                    <td>{{ $departemen->departemen_id }}</td>
                                    <td>{{ $departemen->nama }}</td>
                                    <td>{{ $departemen->deskripsi }}</td>
                                    <td>
                                        <a href="{{ route('departemens.edit', $departemen) }}"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('departemens.destroy', $departemen) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                data-name="{{ $departemen->nama }}">
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
    </div>
@endsection

@section('scripts')
    <script>
        const kompartemensData = @json($kompartemens);

        $(document).ready(function() {
            let table = null;

            // SweetAlert2 delete (uses global Swal already loaded on main layout)
            $('#departemenTable').on('click', '.btn-delete', function(e) {
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
                }).then(result => {
                    if (result.isConfirmed) form.submit();
                });
            });

            function initDataTableOnce() {
                if (table) return;
                table = $('#departemenTable').DataTable({
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    columnDefs: [{
                        width: '12.5%',
                        orderable: false,
                        targets: [5]
                    }]
                });

                // Global custom filter (company + kompartemen)
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'departemenTable') return true;

                    const selectedCompany = $('#companyDropdown').val();
                    const selectedKompartemen = $('#kompartemenDropdown').val();

                    const rowNode = table.row(dataIndex).node();
                    const rowCompany = $(rowNode).data('company-id');
                    const rowKompartemen = ($(rowNode).data('kompartemen-id') || '').toString();

                    if (selectedCompany && rowCompany != selectedCompany) return false;
                    if (selectedKompartemen && rowKompartemen != selectedKompartemen) return false;
                    return true;
                });
            }

            function rebuildKompartemenDropdown(companyCode) {
                $('#kompartemenDropdown').empty()
                    .append('<option value="">-- Semua Kompartemen --</option>');

                if (companyCode) {
                    const filtered = kompartemensData.filter(k => k.company_id == companyCode);
                    filtered.forEach(k => {
                        $('#kompartemenDropdown').append(
                            `<option value="${k.kompartemen_id}">${k.nama}</option>`
                        );
                    });
                    $('#kompartemenDropdown').prop('disabled', false);
                } else {
                    $('#kompartemenDropdown').prop('disabled', true);
                }
            }

            $('#companyDropdown').on('change', function() {
                const companyCode = $(this).val();

                if (!companyCode) {
                    // Reset state
                    $('#departemenTableWrapper').addClass('d-none');
                    $('#preSelectNotice').removeClass('d-none');
                    rebuildKompartemenDropdown('');
                    if (table) {
                        table.search('');
                        table.draw();
                    }
                    return;
                }

                // First time selection: initialize table
                initDataTableOnce();
                $('#departemenTableWrapper').removeClass('d-none');
                $('#preSelectNotice').addClass('d-none');

                rebuildKompartemenDropdown(companyCode);

                // Redraw with new filter criteria
                table.draw();
            });

            $('#kompartemenDropdown').on('change', function() {
                if (table) table.draw();
            });
        });
    </script>
@endsection
