@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Master Data Kompartemen</h2>
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
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <hr class="mt-3 mb-3" style="width: 80%; margin:auto">

        <!-- Table to display all Kompartemen -->
        <table id="kompartemenTable" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    {{-- <th>ID</th>
                    <th>Company Name</th> --}}
                    <th>Nama Kompartemen</th>
                    <th>Deskripsi</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kompartemens as $kompartemen)
                    <tr data-company-id="{{ $kompartemen->company_id }}">
                        {{-- <td>{{ $kompartemen->id }}</td>
                        <td>{{ $kompartemen->company->name ?? 'N/A' }}</td> --}}
                        <td>{{ $kompartemen->name }}</td>
                        <td>{{ $kompartemen->description }}</td>
                        <td>
                            <a href="{{ route('kompartemens.edit', $company) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('kompartemens.destroy', $company) }}" method="POST"
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
        $(document).ready(function() {
            // Initialize DataTable with client-side searching, sorting, and pagination
            if ($.fn.DataTable) { // Check if DataTable is defined
                let table = $('#kompartemenTable').DataTable({
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    columnDefs: [{
                        width: '12.5%',
                        orderable: false,
                        targets: [2] // Disable ordering for the 'Actions' column
                    }]
                });
            } else {
                console.error('DataTable library not loaded.');
            }

            // Filter table based on selected company
            $('#companyDropdown').change(function() {
                let companyId = $(this).val();
                if (companyId) {
                    // Filter rows based on company ID
                    table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                        let row = this.node();
                        if ($(row).data('company-id') == companyId) {
                            $(row).show();
                        } else {
                            $(row).hide();
                        }
                    });
                } else {
                    // Show all rows if no company is selected
                    table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                        let row = this.node();
                        $(row).show();
                    });
                }
                table.draw(); // Redraw the table
            });
        });
    </script>
@endsection
