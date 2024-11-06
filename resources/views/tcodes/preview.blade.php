@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Preview Import Data</h1>

        <!-- Display warnings if any -->
        @if (!empty($warnings))
            <div class="alert alert-warning">
                <ul>
                    @foreach ($warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Display prepared data table -->
        @if (!empty($preparedData))
            <table id="previewTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Company Code</th>
                        <th>Single Role Name</th>
                        <th>Single Role Description</th>
                        <th>Tcode</th>
                        <th>Tcode Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($preparedData as $row)
                        <tr>
                            <td>{{ $row['company_code'] }}</td>
                            <td>{{ $row['single_role_name'] }}</td>
                            <td>{{ $row['single_role_desc'] }}</td>
                            <td>{{ $row['code'] }}</td>
                            <td>{{ $row['deskripsi'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No data to display.</p>
        @endif

        <!-- Confirm Button -->
        <form action="{{ route('tcodes.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="data" value="{{ base64_encode(json_encode($preparedData)) }}">
            <button type="submit" class="btn btn-primary">Confirm Import</button>
        </form>

    </div>
@endsection
<!-- DataTables Integration -->
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#previewTable').DataTable({
                paging: true,
                searching: true,
                ordering: true
            });
        });
    </script>
@endsection
