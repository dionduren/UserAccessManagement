@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- <h1>Preview {{ ucfirst($module) }} Data</h1> --}}

        <h1>Preview Modul - {{ ucfirst(config('dynamic_uploads.modules.' . $module . '.name')) }}</h1>

        <div id="dynamicTable"></div>

        <button id="submit-all" class="btn btn-success mt-3">Submit All</button>

        <!-- Progress Modal -->
        <div id="progress-container" class="my-4" style="display: none;">
            <h5>Uploading Data...</h5>
            <div class="progress">
                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                    aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>

        <div id="back-to-index" class="mt-3" style="display: none;">
            <a href="{{ route('dynamic_upload.upload', ['module' => $module]) }}" class="btn btn-secondary">Back to Upload
                Page</a>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const validationFormatter = function(cell, formatterParams, onRendered) {
            const errors = cell.getValue();
            if (Array.isArray(errors) && errors.length > 0) {
                return `<span style="color:#dc3545">❌ Errors (${errors.length})</span>`;
            } else {
                return `<span style="color:#198754">✅ Valid</span>`;
            }
        };

        const table = new Tabulator("#dynamicTable", {
            layout: "fitColumns",
            ajaxURL: "{{ route('dynamic_upload.preview_data', ['module' => $module]) }}",
            ajaxResponse: function(url, params, response) {
                return response.data || response;
            },
            columns: [
                ...{!! json_encode($columns) !!},
                {
                    title: "Validation",
                    field: "_row_errors",
                    hozAlign: "center",
                    formatter: validationFormatter,
                    headerSort: false
                },
                {
                    title: "Error Details",
                    field: "_row_errors",
                    hozAlign: "left",
                    formatter: function(cell) {
                        const errors = cell.getValue();
                        if (Array.isArray(errors) && errors.length > 0) {
                            return errors.map(err => `• <span style="white-space: pre-wrap;">${err}</span>`)
                                .join("<br>");
                        }
                        return "";
                    },
                    headerSort: false
                },
                {
                    // 👻 Hidden field used only for sorting
                    field: "_row_errors_sort",
                    visible: false
                }
            ],
            pagination: "local",
            paginationSize: 10,
            paginationSizeSelector: [10, 20, 30],
            rowFormatter: function(row) {
                const data = row.getData();
                if (data._row_errors && data._row_errors.length > 0) {
                    row.getElement().style.backgroundColor = "#f8d7da"; // Bootstrap danger bg
                    row.getElement().style.color = "#721c24"; // Bootstrap danger text
                    row.getElement().title = data._row_errors.join("\n");
                }
            },
            initialSort: [{
                column: "_row_errors_sort",
                dir: "desc"
            }]
        });

        const notyf = new Notyf();

        table.on('cellEdited', function(cell) {
            const rowData = cell.getRow().getData();
            const column = cell.getField();
            const value = cell.getValue();
            const rowIndex = rowData._row_index;

            fetch('{{ route('dynamic_upload.update_inline', ['module' => $module]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        row_index: rowIndex,
                        column: column,
                        value: value
                    })
                })
                .then(res => res.json())
                .then(response => {
                    if (response.success) notyf.success('Updated');
                    else notyf.error('Failed');
                });
        });

        $('#submit-all').on('click', function() {
            const progressContainer = $('#progress-container');
            const progressBar = $('#progress-bar');
            const backToIndex = $('#back-to-index');

            progressContainer.show();
            progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
            backToIndex.hide();

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('dynamic_upload.submitAll', ['module' => $module]) }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.onprogress = function(e) {
                const responseText = e.currentTarget.responseText.trim();
                const lines = responseText.split('\n');
                const lastLine = lines[lines.length - 1];

                try {
                    const data = JSON.parse(lastLine);
                    if (data.progress !== undefined) {
                        const progress = Math.min(data.progress, 100);
                        progressBar.css('width', progress + '%').attr('aria-valuenow', progress).text(progress
                            .toFixed(0) + '%');
                    }
                } catch (err) {
                    console.error('Progress parse error:', err);
                }
            };

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    notyf.success('Import completed!');
                    table.clearData();
                    progressBar.text('100%').css('width', '100%');
                    backToIndex.show();
                } else {
                    notyf.error('Submission error');
                }
            };

            xhr.onerror = function() {
                notyf.error('Connection error.');
                progressContainer.hide();
            };

            xhr.send();
        });
    </script>
@endsection
