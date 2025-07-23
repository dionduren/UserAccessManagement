@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2>Preview Import USSM Job Role</h2>
        <form id="confirm-form" action="{{ route('ussm-job-role.confirmImport') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success mb-3">Confirm Import</button>
            <a href="{{ route('ussm-job-role.upload') }}" class="btn btn-secondary mb-3">Cancel</a>
        </form>
        <div id="progress-container" class="my-4" style="display: none;">
            <h5>Uploading Data...</h5>
            <div class="progress">
                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>
        <table id="previewTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th style="background-color: greenyellow">Nama User</th>
                    <th style="background-color: greenyellow">Unit Kerja</th>
                    <th>Job Role ID</th>
                    <th style="background-color: greenyellow">Nama Job Role</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#previewTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('ussm-job-role.previewData') }}",
                columns: [{
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'nik_owner',
                        name: 'nik_owner'
                    },
                    {
                        data: 'unit_kerja',
                        name: 'unit_kerja'
                    },
                    {
                        data: 'job_role_id',
                        name: 'job_role_id'
                    },
                    {
                        data: 'job_role_name',
                        name: 'job_role_name'
                    },
                    {
                        data: 'validation_message',
                        name: 'validation_message',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_sort',
                        name: 'status_sort',
                        visible: false,
                        searchable: false
                    }, // hidden sort column
                ],
                order: [
                    [4, 'desc']
                ], // sort by status_sort descending (status first)
                createdRow: function(row, data) {
                    if (data.row_class === 'row-yellow') {
                        $(row).css('background-color', '#fffbe6');
                    } else if (data.row_class === 'row-red') {
                        $(row).css('background-color', '#ffeaea');
                    } else if (data.row_class === 'row-orange') {
                        $(row).css('background-color', '#fff3e0');
                    }
                },
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
            });

            // Progress bar for confirm import
            const confirmForm = $('#confirm-form');
            const progressContainer = $('#progress-container');
            const progressBar = $('#progress-bar');

            confirmForm.on('submit', function(e) {
                e.preventDefault();
                progressContainer.show();
                progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
                confirmForm.find('button[type="submit"]').hide();
                confirmForm.find('a').hide();

                const xhr = new XMLHttpRequest();
                xhr.open('POST', $(this).attr('action'));
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                xhr.onprogress = function(e) {
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
                            $('<div class="alert alert-success mt-4"><h4>' + data.message +
                                '</h4></div>').insertAfter(confirmForm);
                            $('<div class="d-flex justify-content-between mt-4">' +
                                '<a href="{{ route('ussm-job-role.upload') }}" class="btn btn-primary">Back to Upload Page</a>' +
                                '<a href="{{ route('home') }}" class="btn btn-secondary">Go to Home Page</a>' +
                                '</div>').insertAfter(confirmForm);
                        }
                    } catch (error) {
                        // ignore parse errors for partial responses
                    }
                };

                xhr.onerror = function() {
                    alert('An error occurred while uploading the data.');
                    progressContainer.hide();
                    confirmForm.find('button[type="submit"]').show();
                    confirmForm.find('a').show();
                };

                xhr.onload = function() {
                    if (xhr.status >= 400) {
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            const errorMsg = `<div class="alert alert-danger">
                        <h4>Error:</h4>
                        <ul>
                            <li>${errorResponse.message || 'An unexpected error occurred.'}</li>
                            <li>Details: ${errorResponse.details?.error_message || 'No additional details.'}</li>
                        </ul>
                    </div>`;
                            $('#confirm-form').before(errorMsg);
                        } catch (error) {
                            const errorMsg =
                                '<div class="alert alert-danger">An unexpected error occurred. Please try again or contact support.</div>';
                            $('#confirm-form').before(errorMsg);
                        }
                    }
                };

                xhr.send(new FormData(confirmForm[0]));
            });
        });
    </script>
@endsection
