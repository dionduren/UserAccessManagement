@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Preview Composite and Single Roles</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Display validation errors -->
        @if (session('validationErrors'))
            <div class="alert alert-danger">
                <h4>Validation Errors:</h4>
                <ul>
                    @foreach (session('validationErrors') as $row => $messages)
                        <li><strong>Row {{ $row }}:</strong>
                            <ul>
                                @foreach ($messages as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Table to display parsed data -->
        @if (!empty($parsedData))
            <table id="compositeSingleTable" class="display responsive nowrap table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Company Code</th>
                        <th>Perusahaan</th>
                        <th>Composite Role</th>
                        <th>Single Role</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parsedData as $row)
                        <tr>
                            <td>{{ $row['company_code'] }}</td>
                            <td>{{ $row['company_name'] }}</td>
                            <td>{{ $row['composite_role'] }}</td>
                            <td>{{ $row['single_role'] }}</td>
                            <td>{{ $row['single_role_desc'] ?? 'None' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No data available to preview.</p>
        @endif

        <!-- DataTables Initialization Script -->
        <script>
            $(document).ready(function() {
                $('#compositeSingleTable').DataTable({
                    responsive: true,
                    searching: true,
                    paging: true,
                    ordering: true,
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                });
            });
        </script>


        <!-- Confirmation form -->
        <form action="{{ route('composite_single.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success mt-3">Confirm Import</button>
            <a href="{{ route('composite_single.upload') }}" class="btn btn-danger mt-3">Cancel</a>
        </form>
    </div>
@endsection
