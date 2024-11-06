@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Single Role Import Preview</h2>
        @if (!empty($preparedData))
            <table class="table table-bordered table-hover" id="preview-table">
                <thead>
                    <tr>
                        <th>Composite Role</th>
                        <th>Composite Role Description</th>
                        <th>Single Role</th>
                        <th>Single Role Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($preparedData as $data)
                        <tr>
                            <td>{{ $data['composite_role_name'] ?? 'N/A' }}</td>
                            <td>{{ $data['composite_role_desc'] ?? 'N/A' }}</td>
                            <td>{{ $data['single_role_name'] ?? 'N/A' }}</td>
                            <td>{{ $data['single_role_desc'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No data to preview.</p>
        @endif

        <!-- Confirm Import Button -->
        <form action="{{ route('singleRoles.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="data" value="{{ json_encode($preparedData) }}">
            <button type="submit" class="btn btn-primary">Confirm Import</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#preview-table').DataTable({
                searching: true,
                paging: true,
                ordering: true
            });
        });
    </script>
@endpush
