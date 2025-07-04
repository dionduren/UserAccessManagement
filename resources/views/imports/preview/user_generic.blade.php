@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2>Preview User Generic Data</h2>
        <table class="table table-bordered display responsive nowrap" id="preview-table">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>User Code</th>
                    <th>User Type</th>
                    <th>License Type</th>
                    <th style="background-color:greenyellow">Kompartemen ID</th>
                    <th style="background-color:greenyellow">Kompartemen Name</th>
                    <th style="background-color:greenyellow">Departemen ID</th>
                    <th style="background-color:greenyellow">Departemen Name</th>
                    <th>Last Login</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!-- Progress Bar -->
        <div id="progress-container" class="my-4" style="display: none;">
            <h5>Uploading Data...</h5>
            <div class="progress">
                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>
        <form id="import-form" action="{{ route('user-generic.confirmImport') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Import</button>
        </form>
        <div id="import-success" class="alert alert-success mt-4" style="display:none;"></div>
        <div id="import-error" class="alert alert-danger mt-4" style="display:none;"></div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#preview-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('user-generic.getPreviewData') }}",
                    type: "GET",
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'group'
                    },
                    {
                        data: 'user_code'
                    },
                    {
                        data: 'user_type'
                    },
                    {
                        data: 'license_type'
                    },
                    {
                        data: 'kompartemen_id'
                    },
                    {
                        data: 'kompartemen_name'
                    },
                    {
                        data: 'departemen_id'
                    },
                    {
                        data: 'departemen_name'
                    },
                    {
                        data: 'last_login'
                    },
                    {
                        data: 'valid_from'
                    },
                    {
                        data: 'valid_to'
                    }
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                rowCallback: function(row, data) {
                    // Remove previous classes
                    $(row).removeClass('table-warning table-danger table-both');
                    let hasError = data._row_errors && data._row_errors.length > 0;
                    let hasWarning = data._row_warnings && data._row_warnings.length > 0;

                    // Tooltip content
                    let tooltip = '';
                    if (hasError && hasWarning) {
                        $(row).css({
                            'background-color': '#ff9800',
                            'color': '#000000'
                        }); // orange
                        tooltip = (data._row_errors.join('\n') + '\n' + data._row_warnings.join('\n'))
                            .trim();
                    } else if (hasError) {
                        $(row).css({
                            'background-color': '#f51a0a',
                            'color': '#fff'
                        }); // red
                        tooltip = data._row_errors.join('\n');
                    } else if (hasWarning) {
                        $(row).css({
                            'background-color': '#f0dd0a',
                            'color': '#000000'
                        }); // yellow
                        tooltip = data._row_warnings.join('\n');
                    } else {
                        $(row).css({
                            'background-color': '',
                            'color': ''
                        });
                        tooltip = '';
                    }

                    // Set tooltip for the whole row
                    $(row).attr('title', tooltip);
                }
            });

            // Import form with progress bar
            const importForm = $('#import-form');
            const progressContainer = $('#progress-container');
            const progressBar = $('#progress-bar');
            const importSuccess = $('#import-success');
            const importError = $('#import-error');

            importForm.on('submit', function(e) {
                e.preventDefault();
                progressContainer.show();
                progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
                importForm.find('button[type="submit"]').prop('disabled', true);
                importSuccess.hide();
                importError.hide();

                // Streaming progress
                const xhr = new XMLHttpRequest();
                xhr.open('POST', importForm.attr('action'), true);
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));

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
                            importSuccess.text(data.success).show();
                            importForm.find('button[type="submit"]').prop('disabled', false);
                        }
                    } catch (e) {
                        // ignore parse errors
                    }
                };
                xhr.onerror = function() {
                    importError.text('An error occurred while uploading the data.').show();
                    progressContainer.hide();
                    importForm.find('button[type="submit"]').prop('disabled', false);
                };
                xhr.onload = function() {
                    importForm.find('button[type="submit"]').prop('disabled', false);
                };
                xhr.send(new FormData(importForm[0]));
            });
        });
    </script>
@endsection
