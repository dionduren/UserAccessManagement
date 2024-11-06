@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Tcodes</h1>

        <a href="{{ route('tcodes.create') }}" class="btn btn-primary mb-3">Create New Tcode</a>

        <!-- Button to Access Upload Tcode Page -->
        <a href="{{ route('tcodes.upload') }}" class="btn btn-success mb-3">
            <i class="bi bi-upload"></i> Upload Tcode Data
        </a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <table id="tcodes_table" class="table table-striped">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tcodes as $tcode)
                    <tr>
                        <td>{{ $tcode->company->name ?? 'N/A' }}</td>
                        <td>{{ $tcode->code }}</td>
                        <td>{{ $tcode->deskripsi }}</td>
                        <td>
                            <a href="#" data-id="{{ $tcode->id }}" class="btn btn-info btn-sm show-tcode"
                                data-toggle="modal" data-target="#showTcodeModal">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('tcodes.edit', $tcode) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('tcodes.destroy', $tcode) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal for Tcode Details -->
    <div class="modal fade" id="showTcodeModal" tabindex="-1" aria-labelledby="showTcodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showTcodeModalLabel">Tcode Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-tcode-details">
                    <!-- Tcode details will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#tcodes_table').DataTable({
                searching: true,
                processing: false,
                serverSide: false
            });

            // Show Tcode Details in Modal
            $(document).on('click', '.show-tcode', function(e) {
                e.preventDefault();
                let tcodeId = $(this).data('id');

                // Fetch the tcode details using AJAX
                $.ajax({
                    url: `/tcodes/${tcodeId}`,
                    method: 'GET',
                    success: function(response) {
                        $('#modal-tcode-details').html(response);
                        $('#showTcodeModal').modal('show'); // Open the modal
                    },
                    error: function() {
                        $('#modal-tcode-details').html(
                            '<p class="text-danger">Unable to load tcode details.</p>');
                    }
                });
            });

            $(document).on('click', '.close', function() {
                $('#showTcodeModal').modal('hide');
            });
        });
    </script>
@endsection
