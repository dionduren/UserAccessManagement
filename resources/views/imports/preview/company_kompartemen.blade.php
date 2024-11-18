@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Company-Kompartemen Data Preview</h1>

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

        <!-- Data Table -->
        @if (!empty($validatedData))
            <table id="companyKompartemenTable" class="display responsive nowrap" style="width:100%">
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
                    @foreach ($validatedData as $row)
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
        @else
            <p>No data available to preview.</p>
        @endif

        <!-- DataTables Initialization Script -->
        <script>
            $(document).ready(function() {
                $('#companyKompartemenTable').DataTable({
                    responsive: true,
                    searching: true,
                    paging: true,
                    ordering: true,
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                });
            });
        </script>

        <!-- Confirmation Form -->
        <form action="{{ route('company_kompartemen.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success mt-3">Confirm Import</button>
            <a href="{{ route('company_kompartemen.upload') }}" class="btn btn-danger mt-3">Cancel</a>
        </form>
    </div>
@endsection
