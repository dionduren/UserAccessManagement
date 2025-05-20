@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">{{ $moduleConfig['name'] }} Preview</h1>
        <div id="tabulator-table"></div>
        <button id="submit-all" class="btn btn-success mt-3">Submit All</button>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let table = new Tabulator("#tabulator-table", {
                ajaxURL: "{{ route('dynamic_upload.preview_data', $module) }}",
                ajaxResponse: function(url, params, response) {
                    response.data.sort((a, b) => (b._row_issues_count || 0) - (a._row_issues_count ||
                        0));
                    return response.data;
                },
                rowFormatter: function(row) {
                    const data = row.getData();
                    if (data._row_issues_count > 0) {
                        row.getElement().style.backgroundColor = data._row_errors.length > 0 ?
                            '#ffdddd' // red for errors
                            :
                            '#fff3cd'; // yellow for warnings
                        // row.getElement().title = [...data._row_errors, ...data._row_warnings].join(
                        //     '; ');
                        row.getElement().title = [
                            ...(data._row_errors || []),
                            ...(data._row_warnings || [])
                        ].join('; ');
                    }
                },
                layout: "fitColumns",
                columns: {!! json_encode($columns) !!}.map(col => ({
                    ...col,
                    formatter: function(cell) {
                        const rowData = cell.getRow().getData();
                        const field = cell.getField();
                        const hasError = rowData._cell_errors && rowData._cell_errors[
                            field];
                        const hasWarning = rowData._cell_warnings && rowData._cell_warnings[
                            field];
                        let displayValue = cell.getValue();
                        if (hasError) {
                            return `<span style="color: red;" title="${hasError}">❌ ${displayValue}</span>`;
                        } else if (hasWarning) {
                            return `<span style="color: orange;" title="${hasWarning}">⚠ ${displayValue}</span>`;
                        }
                        return displayValue;
                    }
                })),
                pagination: true,
                paginationSize: 10,
                movableColumns: true,
                height: "500px",
            });


            $('#submit-all').on('click', function() {
                fetch("{{ route('dynamic_upload.submitAll', $module) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            let message =
                                `${data.saved} rows berhasil disimpan.<br>${data.skipped} rows dilewati.<br><br>`;

                            if (data.skipped_details.length > 0) {
                                message += '<div style="text-align: justify;">Skipped rows:<br><ol>';
                                data.skipped_details.forEach(item => {
                                    message +=
                                        `<li>Row ${item.row}: ${item.reasons.join('<br>')}</li>`;
                                });
                                message += '</ol></div>';
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Upload Summary',
                                html: message,
                                width: 600,
                                customClass: {
                                    popup: 'text-left'
                                }
                            });

                            // Optionally reload the table after save
                            // $('#tabulator-table')[0].tabulator.setData();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Failed',
                                text: data.error || 'Unknown error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Submit error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Network or Server Error',
                            text: 'Failed to submit data. Please try again.'
                        });
                    });
            });

        });
    </script>
@endsection
