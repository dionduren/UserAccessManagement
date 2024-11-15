@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Companies</h1>
        <a href="{{ route('companies.create') }}" class="btn btn-primary mb-3">Create New Company</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table id="companiesTable" class="table table-bordered table-hover cell-border hover">
            <thead>
                <tr>
                    <th>Company Code</th>
                    <th>Nama</th>
                    <th>Singkatan</th>
                    <th>Deskripsi</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($companies as $company)
                    <tr>
                        <td>{{ $company->company_code }}</td>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->shortname }}</td>
                        <td>{{ $company->description }}</td>
                        <td>
                            <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('companies.destroy', $company) }}" method="POST" style="display:inline;">
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
            if ($.fn.DataTable) { // Check if DataTable is defined
                $('#companiesTable').DataTable({
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    columnDefs: [{
                        width: '10%',
                        targets: 0
                    }, {
                        width: '20%',
                        targets: 1
                    }, {
                        width: '10%',
                        targets: 2
                    }, {
                        width: '12.5%',
                        orderable: false,
                        targets: [4] // Disable ordering for the 'Actions' column
                    }]
                });
            } else {
                console.error('DataTable library not loaded.');
            }
        });
    </script>
@endsection
