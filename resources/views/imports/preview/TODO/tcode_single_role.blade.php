@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tcode - Single Role Data Preview</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Kompartemen</th>
                    <th>Departemen</th>
                    <th>Single Role</th>
                    <th>Single Role Desc</th>
                    <th>Tcode</th>
                    <th>Tcode Desc</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($paginatedData as $row)
                    <tr>
                        <td>{{ $row['company'] ?? '' }}</td>
                        <td>{{ $row['kompartemen'] ?? '' }}</td>
                        <td>{{ $row['departemen'] ?? '' }}</td>
                        <td>{{ $row['single_role'] ?? '' }}</td>
                        <td>{{ $row['single_role_desc'] ?? '' }}</td>
                        <td>{{ $row['tcode'] ?? '' }}</td>
                        <td>{{ $row['tcode_desc'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $paginatedData->links() }}
    </div>
@endsection
