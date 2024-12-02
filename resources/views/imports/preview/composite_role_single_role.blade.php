@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Preview Composite & Single Roles</h1>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <!-- Datatable -->
        <table id="compositeSingleTable" class="table table-bordered display responsive nowrap">
        </table>

        <!-- Progress Bar -->
        <div id="progress-container" class="my-4" style="display: none;">
            <h5>Uploading Data...</h5>
            <div class="progress">
                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                    aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>

        <!-- Form for Confirmation -->
        <form id="confirm-form" action="{{ route('composite_single.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Import</button>
            <a href="{{ route('composite_single.upload') }}" class="btn btn-secondary">Cancel</a>
        </form>

        <!-- Redirect Buttons after Success -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('composite_single.upload') }}" class="btn btn-primary">Back to Upload Page</a>
                <a href="{{ route('home') }}" class="btn btn-secondary">Go to Home Page</a>
            </div>
        @endif

        <!-- Error Upload Info -->
        <div id="error-upload-info" style="display: none;">
            <div class="alert alert-danger">
                <span id="error-message"></span>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressContainer = $('#progress-container');
            const progressBar = $('#progress-bar');
            const confirmForm = $('#confirm-form');
            const table = $('#compositeSingleTable');
            const errorUploadInfo = $('#error-upload-info');
            const errorMessage = $('#error-message');

            // TADI BERHENTI DI SIN

            $('#compositeSingleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('composite_single.preview_data') }}",
                    type: "GET",
                },
                columns: [{
                        data: 'company',
                        name: 'company',
                        title: 'Company ID'
                    }, {
                        data: 'composite_role',
                        name: 'composite_role',
                        title: 'Composite Role'
                    },
                    {
                        data: 'single_role',
                        name: 'single_role',
                        title: 'Single Role'
                    },
                    {
                        data: 'description',
                        name: 'description',
                        title: 'Description'
                    },
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
            });

            // Show progress bar on form submission
            confirmForm.on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission
                progressContainer.show();
                progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');


                // Hide the buttons
                confirmForm.find('button[type="submit"]').hide();
                confirmForm.find('a').hide();

                // Perform AJAX request to process the import
                $.ajax({
                    url: confirmForm.attr('action'),
                    type: 'POST',
                    data: confirmForm.serialize(),
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();

                        // Handle progress updates from the server
                        xhr.onprogress = function(event) {
                            if (event.currentTarget.responseText) {
                                try {
                                    // Parse the latest chunk of data
                                    const responseChunks = event.currentTarget.responseText
                                        .split('\n');
                                    const lastChunk = responseChunks[responseChunks.length -
                                        2]; // Last non-empty chunk
                                    const progressData = JSON.parse(lastChunk);

                                    // Update progress bar
                                    if (progressData.progress) {
                                        const progress = progressData.progress;
                                        progressBar.css('width', progress + '%').attr(
                                            'aria-valuenow', progress).text(progress +
                                            '%');
                                    }
                                } catch (e) {
                                    console.error('Error parsing progress:', e);
                                }
                            }
                        };
                        return xhr;
                    },
                    success: function(response) {

                        // Show success message
                        $('<div class="alert alert-success mt-4"><h4>' + response.success +
                            '</h4></div>').insertAfter(confirmForm);

                        // Show redirect buttons
                        $('<div class="d-flex justify-content-between mt-4">' +
                            '<a href="{{ route('composite_single.upload') }}" class="btn btn-primary">Back to Upload Page</a>' +
                            '<a href="{{ route('home') }}" class="btn btn-secondary">Go to Home Page</a>' +
                            '</div>').insertAfter(confirmForm);

                        // Update progress bar to 100%
                        progressBar.css('width', '100%').attr('aria-valuenow', 100).text(
                            '100%');
                    },
                    error: function(xhr) {
                        console.log('Error: ' + (xhr.responseJSON?.error ||
                            'An unknown error occurred.'));
                        progressContainer.hide();
                        // Re-show the buttons if there's an error
                        confirmForm.find('button[type="submit"]').show();
                        confirmForm.find('a').show();
                    }
                });
            });
        });
    </script>
@endsection
