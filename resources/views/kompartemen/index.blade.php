@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Master Data Kompartemen</h2>

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
        <table id="kompartemenTable" class="table table-bordered table-hover cell-border mt-3">
            <thead>
                <tr>
                    {{-- <th>ID</th>
                    <th>Company Name</th> --}}
                    <th>Kompartemen Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kompartemens as $kompartemen)
                    <tr data-company-id="{{ $kompartemen->company_id }}">
                        {{-- <td>{{ $kompartemen->id }}</td>
                        <td>{{ $kompartemen->company->name ?? 'N/A' }}</td> --}}
                        <td>{{ $kompartemen->name }}</td>
                        <td>{{ $kompartemen->description }}</td>
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
            let table = $('#kompartemenTable').DataTable();

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
