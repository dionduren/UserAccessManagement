@extends('layouts.app')

@section('content')
    <div class="container">
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
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
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
                    {{-- <th>ID</th>
                    <th>Company Name</th>
                    <th>Kompartemen Name</th> --}}
                    <th>Nama Departemen</th>
                    <th>Deskripsi</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departemens as $departemen)
                    <tr data-company-id="{{ $departemen->company_id }}"
                        data-kompartemen-id="{{ $departemen->kompartemen_id }}">
                        {{-- <td>{{ $departemen->id }}</td>
                        <td>{{ $departemen->company->name ?? 'N/A' }}</td>
                        <td>{{ $departemen->kompartemen->name ?? 'N/A' }}</td> --}}
                        <td>{{ $departemen->name }}</td>
                        <td>{{ $departemen->description }}</td>
                        <td>
                            <a href="{{ route('departemens.edit', $company) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('departemens.destroy', $company) }}" method="POST"
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
                let table = $('#departemenTable').DataTable({
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


            // Fetch Kompartemen based on selected company
            $('#companyDropdown').change(function() {
                let companyId = $(this).val();
                if (companyId != '') {
                    $.ajax({
                        url: '/get-kompartemen', // Adjust the route as per your configuration
                        method: 'GET',
                        data: {
                            company_id: companyId
                        },
                        success: function(data) {
                            $('#kompartemenDropdown').empty().append(
                                '<option value="">-- Semua Kompartemen --</option>');
                            $.each(data, function(key, value) {
                                $('#kompartemenDropdown').append('<option value="' +
                                    value.id + '">' + value.name + '</option>');
                            });
                            $('#kompartemenDropdown').prop('disabled', false);
                        },
                        error: function() {
                            alert('Failed to fetch Kompartemen.');
                        }
                    });
                } else {
                    $('#kompartemenDropdown').empty().append(
                        '<option value="">-- Semua Kompartemen --</option>');
                    $('#kompartemenDropdown').prop('disabled', true);
                    table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                        let row = this.node();
                        $(row).show();
                    });
                    table.draw();
                }
            });

            // Filter table based on selected company and kompartemen
            $('#kompartemenDropdown').change(function() {
                let companyId = $('#companyDropdown').val();
                let kompartemenId = $(this).val();

                table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                    let row = this.node();
                    if ((!companyId || $(row).data('company-id') == companyId) &&
                        (!kompartemenId || $(row).data('kompartemen-id') == kompartemenId)) {
                        $(row).show();
                    } else {
                        $(row).hide();
                    }
                });
                table.draw(); // Redraw the table
            });
        });
    </script>
@endsection
