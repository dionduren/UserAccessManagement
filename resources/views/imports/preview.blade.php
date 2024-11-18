@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Data Preview</h1>

        <!-- Display Company-Kompartemen-Departemen-Job Role-Composite Role Data -->
        @if (isset($companyKompartemenData) && $companyKompartemenData->isNotEmpty())
            <h2>Company-Kompartemen-Departemen-Job Role-Composite Role</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Kompartemen</th>
                        <th>Departemen</th>
                        <th>Job Function</th>
                        <th>Composite Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($companyKompartemenData as $row)
                        <tr>
                            <td>{{ $row['company'] ?? '' }}</td>
                            <td>{{ $row['kompartemen'] ?? '' }}</td>
                            <td>{{ $row['departemen'] ?? '' }}</td>
                            <td>{{ $row['job_function'] ?? '' }}</td>
                            <td>{{ $row['composite_role'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $companyKompartemenData->links() }}
        @endif

        <!-- Display Composite Role and Single Role Data -->
        @if (isset($compositeRoleSingleRoleData) && $compositeRoleSingleRoleData->isNotEmpty())
            <h2>Composite Role and Single Role</h2>
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
                    @foreach ($compositeRoleSingleRoleData as $row)
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
            {{ $compositeRoleSingleRoleData->links() }}
        @endif

        <!-- Display Tcode and Single Role Data -->
        @if (isset($tcodeSingleRoleData) && $tcodeSingleRoleData->isNotEmpty())
            <h2>Tcode and Single Role</h2>
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
                    @foreach ($tcodeSingleRoleData as $row)
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
            {{ $tcodeSingleRoleData->links() }}
        @endif

        <!-- Confirmation Form -->
        <form action="{{ route('excel.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Import</button>
            <a href="{{ route('excel.upload') }}" class="btn btn-danger">Cancel</a>
        </form>
    </div>
@endsection
