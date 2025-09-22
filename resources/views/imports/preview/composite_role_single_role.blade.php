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
            const confirmForm = $('#confirm-form');
            const progressContainer = $('#progress-container');
            const progressBar = $('#progress-bar');
            const errorUploadInfo = $('#error-upload-info');
            const errorMessage = $('#error-message');

            $('#compositeSingleTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('composite_single.preview_data') }}",
                    type: "GET",
                },
                columns: [{
                        data: 'company_code',
                        title: 'Company Code'
                    }, {
                        data: 'company_name',
                        title: 'Perusahaan'
                    }, {
                        data: 'composite_role',
                        title: 'Composite Role'
                    }, {
                        data: 'composite_role_description',
                        title: 'Description'
                    },
                    {
                        data: 'single_role',
                        title: 'Single Role'
                    },
                    {
                        data: 'single_role_description',
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

                // Start polling for progress
                // Perform AJAX request with streaming
                const xhr = new XMLHttpRequest();
                xhr.open('POST', confirmForm.attr('action'), true);
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));

                // Event listener for progress updates
                xhr.onprogress = function(event) {
                    const responseText = event.currentTarget.responseText;
                    const lines = responseText.trim().split('\n');
                    const lastLine = lines[lines.length - 1];

                    try {
                        const data = JSON.parse(lastLine);

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
                                '<a href="{{ route('composite_single.upload') }}" class="btn btn-primary">Back to Upload Page</a>' +
                                '<a href="{{ route('home') }}" class="btn btn-secondary">Go to Home Page</a>' +
                                '</div>').insertAfter(confirmForm);
                        }
                    } catch (e) {
                        console.error('Error parsing progress:', e);
                    }
                };

                // Event listener for errors
                xhr.onerror = function() {
                    alert('An error occurred while uploading the data.');
                    progressContainer.hide();
                    confirmForm.find('button[type="submit"]').show();
                    confirmForm.find('a').show();
                };

                xhr.send(new FormData(confirmForm[0]));


            });
        });
    </script>
@endsection
