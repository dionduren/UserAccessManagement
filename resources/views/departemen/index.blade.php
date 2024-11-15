@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Master Data Departemen</h2>

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
                    <th>Departemen Name</th>
                    <th>Description</th>
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
            let table = $('#departemenTable').DataTable();

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
