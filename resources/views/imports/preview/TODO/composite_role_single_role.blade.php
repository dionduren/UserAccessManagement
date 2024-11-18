@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Composite Role - Single Role Data Preview</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Single Role</th>
                    <th>Description</th>
                    <th>Company</th>
                    <th>Kompartemen</th>
                    <th>Departemen</th>
                    <th>Composite Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($paginatedData as $row)
                    <tr>
                        <td>{{ $row['single_role'] ?? '' }}</td>
                        <td>{{ $row['description'] ?? '' }}</td>
                        <td>{{ $row['company'] ?? '' }}</td>
                        <td>{{ $row['kompartemen'] ?? '' }}</td>
                        <td>{{ $row['departemen'] ?? '' }}</td>
                        <td>{{ $row['composite_role'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $paginatedData->links() }}
    </div>
@endsection
