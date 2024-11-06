@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tcode Preview</h1>

        <!-- Missing Roles Section -->
        @if (!empty($missingRolesSummary))
            <div class="alert alert-warning">
                <h5>Missing Single Roles</h5>
                <ul>
                    @foreach ($missingRolesSummary as $roleName => $count)
                        <li>{{ $roleName }} - Missing in {{ $count }} row(s)</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form for Preview and Confirm Upload -->
        <form action="{{ route('tcodes.confirm') }}" method="POST">
            @csrf
            <!-- Table for Preview Data -->
            <div id="previewContainer">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Single Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tcodes as $index => $row)
                            <tr>
                                <td>
                                    <input type="hidden" name="data[{{ $index }}][company_id]"
                                        value="{{ $row['company_id'] }}">
                                    {{ $row['company_id'] }}
                                </td>
                                <td>
                                    <input type="hidden" name="data[{{ $index }}][code]" value="{{ $row['code'] }}">
                                    {{ $row['code'] }}
                                </td>
                                <td>
                                    <input type="hidden" name="data[{{ $index }}][deskripsi]"
                                        value="{{ $row['deskripsi'] }}">
                                    {{ $row['deskripsi'] }}
                                </td>
                                <td>
                                    <input type="hidden" name="data[{{ $index }}][single_roles]"
                                        value="{{ isset($row['single_roles']) && is_array($row['single_roles']) ? implode(',', $row['single_roles']) : '' }}">

                                    {{-- Display Single Roles, checking if each role exists --}}
                                    @if (isset($row['single_roles']) && is_array($row['single_roles']))
                                        {{ implode(
                                            ', ',
                                            array_map(function ($roleId) {
                                                // Check if role exists
                                                $role = \App\Models\SingleRole::find($roleId);
                                                return $role ? $role->nama : 'Missing Role';
                                            }, $row['single_roles']),
                                        ) }}
                                    @else
                                        No Roles Assigned
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            <!-- Confirm Upload Button -->
            <button type="submit" class="btn btn-success mt-3">Confirm Upload</button>

            <!-- Go Back to Upload Page Button -->
            <a href="{{ route('tcodes.upload') }}" class="btn btn-secondary mt-3">Go Back to Upload Page</a>
        </form>

    </div>
@endsection
