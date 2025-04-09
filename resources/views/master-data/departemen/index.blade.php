@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2>Master Data Departemen</h2>

        <a href="{{ route('departemens.create') }}" class="btn btn-primary mb-3">Buat Info Departemen Baru</a>

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

        <!-- Dropdown for Kompartemen Selection -->
        <div class="form-group mb-3">
            <label for="kompartemenDropdown">Pilih Kompartemen</label>
            <select id="kompartemenDropdown" class="form-control" disabled>
                <option value="">-- Semua Kompartemen --</option>
            </select>
        </div>

        <hr class="mt-3 mb-3" style="width: 80%; margin:auto">

        <!-- Table to display all Departemen -->
        <table id="departemenTable" class="table table-bordered table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>Perusahaan</th>
                    <th>Kompartemen</th>
                    <th>Departemen</th>
                    <th>Deskripsi</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departemens as $departemen)
                    <tr data-company-id="{{ $departemen->company_id }}"
                        data-kompartemen-id="{{ $departemen->kompartemen_id ?? '' }}">
                        <td>{{ $departemen->company->nama ?? 'N/A' }}</td>
                        <td>{{ $departemen->kompartemen->nama ?? 'N/A' }}</td>
                        <td>{{ $departemen->nama }}</td>
                        <td>{{ $departemen->deskripsi }}</td>
                        <td>
                            <a href="{{ route('departemens.edit', $departemen) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('departemens.destroy', $departemen) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        const kompartemensData = @json($kompartemens);

        $(document).ready(function() {
            let table = $('#departemenTable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                columnDefs: [{
                    width: '12.5%',
                    orderable: false,
                    targets: [4] // Disable ordering for the 'Actions' column
                }]
            });

            // Load all kompartemen data from the embedded JavaScript variable
            let allKompartemens = kompartemensData;

            // Handle company selection change
            $('#companyDropdown').change(function() {
                let selectedCompanyId = $(this).val();

                // Filter and update kompartemen dropdown based on selected company
                $('#kompartemenDropdown').empty().append(
                    '<option value="">-- Semua Kompartemen --</option>');
                if (selectedCompanyId) {
                    let filteredKompartemens = allKompartemens.filter(k => k.company_id ==
                        selectedCompanyId);
                    filteredKompartemens.forEach(k => {
                        $('#kompartemenDropdown').append('<option value="' + k.id + '">' + k.nama +
                            '</option>');
                    });
                    $('#kompartemenDropdown').prop('disabled', false);
                } else {
                    $('#kompartemenDropdown').prop('disabled', true);
                }

                // Apply DataTable filtering for departemen based on selected company
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    let rowCompanyId = $(table.row(dataIndex).node()).data('company-id');
                    if (!selectedCompanyId || rowCompanyId == selectedCompanyId) {
                        return true;
                    }
                    return false;
                });
                table.draw(); // Redraw table with applied filter
                $.fn.dataTable.ext.search.pop(); // Remove the filter to avoid stacking
            });

            // Handle kompartemen selection change
            $('#kompartemenDropdown').change(function() {
                let selectedKompartemenId = $(this).val();

                // Apply DataTable filtering for departemen based on selected kompartemen
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    let rowKompartemenId = $(table.row(dataIndex).node()).data('kompartemen-id') ||
                        '';
                    if (!selectedKompartemenId || rowKompartemenId == selectedKompartemenId) {
                        return true;
                    }
                    return false;
                });
                table.draw(); // Redraw table with applied filter
                $.fn.dataTable.ext.search.pop(); // Remove the filter to avoid stacking
            });
        });
    </script>
@endsection
