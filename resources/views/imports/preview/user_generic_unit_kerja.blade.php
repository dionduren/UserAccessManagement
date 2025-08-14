@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2>Preview Import User Generic Unit Kerja</h2>
        <table id="preview-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>User Cost Center</th>
                    <th>Kompartemen ID</th>
                    <th>Kompartemen</th>
                    <th>Departemen ID</th>
                    <th>Departemen</th>
                    <th>Errors</th>
                    <th>Warnings</th>
                </tr>
            </thead>
        </table>
        <form id="import-form" action="{{ route('user-generic-unit-kerja.confirmImport') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success mt-3" id="import-btn">Import Data</button>
        </form>
        <div class="mt-3" id="progress-container" style="display:none;">
            <div class="progress">
                <div id="import-progress" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
            </div>
        </div>
        <div class="mt-3">
            <div id="import-message"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $('#preview-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '{{ route('user-generic-unit-kerja.getPreviewData') }}',
                columns: [{
                        data: 'user_cc'
                    },
                    {
                        data: 'kompartemen_id'
                    },
                    {
                        data: 'kompartemen_nama'
                    },
                    {
                        data: 'departemen_id'
                    },
                    {
                        data: 'departemen_nama'
                    },
                    {
                        data: '_row_errors',
                        render: data => data ? data.join('<br>') : ''
                    },
                    {
                        data: '_row_warnings',
                        render: data => data ? data.join('<br>') : ''
                    },
                ],
                rowCallback: function(row, data) {
                    const hasError = data._row_errors && data._row_errors.length > 0;
                    const hasWarning = data._row_warnings && data._row_warnings.length > 0;
                    $(row).removeClass('table-danger table-warning table-warning-orange');
                    if (hasError && hasWarning) {
                        $(row).css('background-color', '#FFA500'); // orange
                    } else if (hasError) {
                        $(row).addClass('table-danger');
                    } else if (hasWarning) {
                        $(row).addClass('table-warning');
                    }
                },
                order: [
                    [5, 'desc'], // Errors column
                    [6, 'desc'] // Warnings column
                ],
                createdRow: function(row, data) {
                    // Ensure background color persists after sorting/searching
                    const hasError = data._row_errors && data._row_errors.length > 0;
                    const hasWarning = data._row_warnings && data._row_warnings.length > 0;
                    if (hasError && hasWarning) {
                        $(row).css('background-color', '#FFA500'); // orange
                    }
                }
            });

            // Progress bar and success message logic
            $('#import-form').on('submit', function(e) {
                e.preventDefault();
                $('#import-btn').prop('disabled', true);
                $('#progress-container').show();
                $('#import-progress').css('width', '0%').text('0%');
                $('#import-message').html('');

                fetch("{{ route('user-generic-unit-kerja.confirmImport') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                            'Accept': 'text/event-stream'
                        }
                    })
                    .then(response => {
                        const reader = response.body.getReader();
                        let buffer = '';

                        function read() {
                            return reader.read().then(({
                                done,
                                value
                            }) => {
                                if (done) return;
                                buffer += new TextDecoder().decode(value);
                                let lines = buffer.split('\n');
                                buffer = lines.pop(); // last line may be incomplete
                                lines.forEach(line => {
                                    if (!line.trim()) return;
                                    try {
                                        const data = JSON.parse(line);
                                        if (data.progress !== undefined) {
                                            $('#import-progress').css('width', data
                                                .progress + '%').text(data
                                                .progress + '%');
                                        }
                                        if (data.success) {
                                            $('#import-progress').css('width', '100%')
                                                .text('100%');
                                            $('#import-message').html(
                                                '<div class="alert alert-success">' +
                                                data.message + '</div>');
                                            setTimeout(() => {
                                                window.location.href = data
                                                    .redirect;
                                            }, 1500);
                                        }
                                    } catch (e) {}
                                });
                                return read();
                            });
                        }
                        return read();
                    })
                    .catch(err => {
                        $('#import-message').html(
                            '<div class="alert alert-danger">Import failed.</div>');
                    })
                    .finally(() => {
                        $('#import-btn').prop('disabled', false);
                    });
            });
        });
    </script>
@endsection
