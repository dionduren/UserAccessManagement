{{-- @extends('layouts.app')

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
        <form action="{{ route('composite_single.confirm') }}" method="POST">
            @csrf

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Single Role</th>
                        <th>Description</th>
                        <th>Composite Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $row)
                        <tr>
                            <td>{{ $row['single_role'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['composite_role'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="btn btn-success">Confirm Import</button>
            <a href="{{ route('composite_single.upload') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection --}}

@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Preview Composite & Single Roles</h1>

        <table id="compositeSingleTable" class="table table-bordered display responsive nowrap">
            <thead>
                <tr>
                    <th>Company ID</th>
                    <th>Composite Role</th>
                    <th>Single Role</th>
                    <th>Single Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row['company'] }}</td>
                        <td>{{ $row['composite_role'] }}</td>
                        <td>{{ $row['single_role'] }}</td>
                        <td>{{ $row['description'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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

        <form action="{{ route('composite_single.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Import</button>
            <a href="{{ route('composite_single.upload') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
