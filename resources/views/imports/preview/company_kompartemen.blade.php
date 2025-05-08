@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Data Preview Job Role - Composite Role</h1>

        <!-- Error Display -->
        @if (session('validationErrors') || session('error'))
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
                    <!-- Validation Errors -->
                    @if (session('validationErrors'))
                        @foreach (session('validationErrors') as $row => $messages)
                            <li>Row {{ $row }}:
                                <ul>
                                    @foreach ($messages as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    @endif

                    <!-- General Error -->
                    @if (session('error'))
                        <li>{{ session('error') }}</li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Laravel Validation Errors -->
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

        <table id="previewTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Company Code</th>
                    <th>Perusahaan</th>
                    <th>Kompartemen ID</th>
                    <th>Kompartemen</th>
                    <th>Departemen ID</th>
                    <th>Departemen</th>
                    <th>Job Function</th>
                    <th>Description</th>
                </tr>
            </thead>
        </table>

        <!-- Progress Bar -->
        <div id="progress-container" class="my-4" style="display: none;">
            <h5>Uploading Data...</h5>
            <div class="progress">
                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>

        <!-- Form for Confirmation -->
        <form id="confirm-form" action="{{ route('company_kompartemen.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Import</button>
            <a href="{{ route('company_kompartemen.upload') }}" class="btn btn-secondary">Cancel</a>
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

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const confirmForm = $('#confirm-form');
            const progressContainer = $('#progress-container');
            const progressBar = $('#progress-bar');

            $('#previewTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('company_kompartemen.preview_data') }}",
                columns: [{
                        data: 'company_code',
                        title: 'Company Code'
                    }, {
                        data: 'company_name',
                        title: 'Perusahaan'
                    },
                    {
                        data: 'kompartemen_id',
                        title: 'Kompartemen ID'
                    },
                    {
                        data: 'kompartemen',
                        title: 'Kompartemen'
                    },
                    {
                        data: 'departemen_id',
                        title: 'Departemen ID'
                    },
                    {
                        data: 'departemen',
                        title: 'Departemen'
                    },
                    {
                        data: 'job_function',
                        title: 'Job Role'
                    },
                    {
                        data: 'composite_role',
                        title: 'Composite Role'
                    }
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

                progressContainer.show(); // Show the progress container
                progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%'); // Reset progress bar

                // Hide the buttons
                confirmForm.find('button[type="submit"]').hide();
                confirmForm.find('a').hide();

                // Start polling for progress
                // Perform AJAX request with streaming
                const xhr = new XMLHttpRequest();
                xhr.open('POST', $(this).attr('action')); // Set the request endpoint
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));

                // Event listener for progress updates
                xhr.onprogress = function(e) {
                    const responseText = event.currentTarget.responseText;
                    const lines = responseText.trim().split('\n');
                    const lastLine = lines[lines.length - 1];

                    try {
                        const data = JSON.parse(lastLine); // Parse the JSON data

                        if (data.progress !== undefined) {
                            const progress = data.progress;
                            progressBar.css('width', progress + '%').attr('aria-valuenow', progress)
                                .text(progress + '%');
                        }

                        if (data.success) {
                            progressBar.css('width', '100%').attr('aria-valuenow', 100).text('100%');

                            $('<div class="alert alert-success mt-4"><h4>' + data.success +
                                '</h4></div>').insertAfter(confirmForm);

                            $('<div class="d-flex justify-content-between mt-4">' +
                                '<a href="{{ route('company_kompartemen.upload') }}" class="btn btn-primary">Back to Upload Page</a>' +
                                '<a href="{{ route('home') }}" class="btn btn-secondary">Go to Home Page</a>' +
                                '</div>').insertAfter(confirmForm);
                        }

                    } catch (error) {
                        // Log an error message if parsing fails
                        console.error('Error parsing progress response:', error);

                        // Optional: Display the invalid data for debugging
                        console.error('Invalid JSON:', lastLine);
                    }
                };

                // Event listener for errors
                xhr.onerror = function() {

                    alert('An error occurred while uploading the data.');
                    progressContainer.hide();
                    confirmForm.find('button[type="submit"]').show();
                    confirmForm.find('a').show();

                    const errorMsg =
                        '<div class="alert alert-danger">An error occurred while processing the request. Please try again or contact support.</div>';
                    $('#confirm-form').before(errorMsg); // Insert the error message above the form
                };

                // Event listener for completion
                xhr.onload = function() {
                    if (xhr.status >= 400) { // Check for HTTP error responses
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            const errorMsg = `<div class="alert alert-danger">
                    <h4>Error:</h4>
                    <ul>
                        <li>${errorResponse.message || 'An unexpected error occurred.'}</li>
                        <li>Details: ${errorResponse.details?.error_message || 'No additional details.'}</li>
                    </ul>
                </div>`;
                            $('#confirm-form').before(errorMsg); // Insert error above the form
                        } catch (error) {
                            console.error('Error parsing error response:', error);
                            const errorMsg =
                                '<div class="alert alert-danger">An unexpected error occurred. Please try again or contact support.</div>';
                            $('#confirm-form').before(errorMsg);
                        }
                    }
                };

                // Send the form data
                // xhr.send(new FormData(this));
                xhr.send(new FormData(confirmForm[0]));
            });

        });
    </script>
@endsection
