@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- General Error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Master Data Tcodes</h2>
            </div>
            <div class="card-body">

                <!-- Trigger button for Create Modal -->
                <button type="button" id="triggerCreateModal" class="btn btn-primary mb-3">
                    Create New Tcode
                </button>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <table id="tcodes_table" class="table table-bordered table-striped table-hover cell-border mt-3">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            {{-- <th>Modul SAP</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- <!-- Modal for Tcode Create & Edit -->
    <div id="modalContainer"></div> --}}

    <!-- Modal for Tcode Details -->
    <div class="modal fade" id="TcodeModal" tabindex="-1" aria-labelledby="TcodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="TcodeModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="TcodeModalBody">
                    <!-- Tcode details will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable with server-side processing
            $('#tcodes_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('tcodes.data') }}',
                columns: [{
                        data: 'code',
                        name: 'code',
                        title: 'Code'
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi',
                        title: 'Description'
                    },
                    // {
                    //     data: 'sap_module',
                    //     name: 'sap_module',
                    //     title: 'Modul SAP'
                    // },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        title: 'Actions'
                    }
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
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
                            '<p class="text-danger">Unable to load tcode details.</p>'
                        );
                    },
                });
            });

            // Function to load modal content dynamically
            function loadModalContent(url, title) {
                $('#TcodeModalLabel').text(title); // Set modal title
                $('#TcodeModalBody').html(
                    '<div class="text-center">Loading...</div>'); // Temporary loading state
                $('#TcodeModal').modal('show'); // Show the modal

                $.get(url, function(data) {
                    $('#TcodeModalBody').html(data); // Populate modal-body with received content
                }).fail(function() {
                    alert('Failed to load data. Please try again.');
                });
            }

            // Handle Create Modal
            $('#triggerCreateModal').on('click', function() {
                loadModalContent('{{ route('tcodes.create') }}', 'Create TCode');
            });

            // Handle Edit Modal
            $(document).on('click', '.edit-tcode', function() {
                const roleId = $(this).data('id');
                const url = `/tcodes/${roleId}/edit`;
                loadModalContent(url, 'Edit Tcode');
            });

            // Handle Show Details Modal
            $(document).on('click', '.show-tcode', function() {
                const roleId = $(this).data('id');
                const url = `/tcodes/${roleId}`;
                loadModalContent(url, 'Tcode Details');
            });

            // AJAX Form Submission for Create
            $(document).on('submit', '#createTcodeForm', function(e) {
                e.preventDefault();
                let form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#TcodeModal').modal('hide');
                            $('#tcodes_table').DataTable().ajax.reload();
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    },
                });
            });

            // AJAX Form Submission for Edit
            $(document).on('submit', '#editTcodeForm', function(e) {
                e.preventDefault();
                let form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#TcodeModal').modal('hide');
                            $('#tcodes_table').DataTable().ajax.reload();
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    },
                });
            });

            $(document).on('click', '.close', function() {
                $('#TcodeModal').modal('hide');
            });
        });
    </script>
@endsection
