@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Preview User NIK</h1>

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

        <div id="NIKJobRoleTable"></div>

        <button id="submit-all" class="btn btn-primary mt-3">Submit All</button>

        <form id="confirm-form" action="{{ route('user-nik.upload.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success mt-3">Confirm Import</button>
            <a href="{{ route('user-nik.upload.form') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        const companies = {!! json_encode(\App\Models\Company::select('id', 'name', 'shortname')->get()) !!};

        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top'
            }
        });

        $(function() {
            const companyOptions = companies.map(company => ({
                value: company.id,
                label: `${company.shortname}`
            }));

            const companyLookup = {};
            companyOptions.forEach(opt => {
                companyLookup[opt.value] = opt.label;
            });

            const table = new Tabulator('#NIKJobRoleTable', {
                layout: 'fitColumns',
                ajaxURL: "{{ route('user-nik.upload.preview_data') }}",
                ajaxResponse: function(url, params, response) {
                    return response.data || response;
                },
                pagination: 'local',
                paginationSize: 10,
                paginationSizeSelector: [5, 10, 15],
                columns: [{
                        title: 'Group',
                        field: 'group',
                        editor: 'list',
                        editorParams: {
                            values: companyOptions,
                            autocomplete: true
                        },
                        formatter: "lookup",
                        formatterParams: companyLookup,
                        headerFilter: "list",
                        headerFilterParams: {
                            values: companyOptions.map(opt => ({
                                label: opt.label,
                                value: opt.value
                            })),
                            clearable: true,
                        },
                        headerFilterPlaceholder: "Select Group",
                        headerFilterFunc: "="

                    },
                    {
                        title: 'User Code',
                        field: 'user_code',
                        editor: 'input',
                        headerFilter: "input",
                        headerFilterPlaceholder: "Search NIK"
                    },
                    {
                        title: 'License Type',
                        field: 'license_type',
                        editor: 'input',
                        headerFilter: "input",
                        headerFilterPlaceholder: "Search License"
                    },
                    {
                        title: 'Last Login',
                        field: 'last_login',
                        editor: 'date',
                        headerFilter: "input",
                        headerFilterPlaceholder: "YYYY-MM-DD"
                    },
                    {
                        title: 'Valid From',
                        field: 'valid_from',
                        editor: 'date',
                        headerFilter: "input",
                        headerFilterPlaceholder: "YYYY-MM-DD"
                    },
                    {
                        title: 'Valid To',
                        field: 'valid_to',
                        editor: 'date',
                        headerFilter: "input",
                        headerFilterPlaceholder: "YYYY-MM-DD"
                    },
                    {
                        title: 'Actions',
                        formatter: function() {
                            return '<button class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Submit</button>';
                        },
                        cellClick: function(e, cell) {
                            const rowComponent = cell.getRow();
                            const rowData = rowComponent.getData();
                            const $btn = $(e.currentTarget).find('button');

                            // Disable the button and show a loading state
                            // $btn.prop('disabled', true).html(
                            //     '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i>'
                            // );

                            $.ajax({
                                url: '{{ route('user-nik.upload.submitSingle') }}',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify(rowData),
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function(response) {
                                    notyf.success(response.message);

                                    // Remove the submitted row from the table
                                    rowComponent.delete();
                                },
                                error: function(xhr) {
                                    notyf.error(xhr.responseJSON?.message ||
                                        'Unknown error');
                                }
                            });
                        },
                        hozAlign: "center",
                        width: 150
                    }
                ],
            });


            // 🟢 Attach event AFTER table initialization:
            table.on('cellEdited', function(cell) {
                const rowData = cell.getRow().getData();
                const column = cell.getField();
                const value = cell.getValue();
                const rowIndex = rowData._row_index ?? cell.getRow()
                    .getPosition(); // fallback if _row_index missing

                fetch('{{ route('user-nik.upload.update-inline-session') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({
                            row_index: rowIndex,
                            column: column,
                            value: value
                        })
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            console.log('Session data updated successfully.');
                        } else {
                            console.error('Failed to update:', response.error);
                        }
                    })
                    .catch(err => console.error('Error:', err));
            });

            // 🟢 Submit All Rows w/ Progress Bar
            $('#submit-all').on('click', function() {
                const confirmForm = document.getElementById('confirm-form');
                const progressContainer = document.getElementById('progress-container');
                const progressBar = document.getElementById('progress-bar');

                progressContainer.style.display = 'block';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', confirmForm.action);
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));

                xhr.onprogress = function(e) {
                    const lines = e.currentTarget.responseText.trim().split('\n');
                    const lastLine = lines[lines.length - 1];

                    try {
                        const data = JSON.parse(lastLine);
                        if (data.progress !== undefined) {
                            progressBar.style.width = data.progress + '%';
                            progressBar.textContent = data.progress.toFixed(0) + '%';
                        }

                        if (data.progress >= 100) {
                            progressBar.textContent = '100%';
                            location.href = "{{ route('user-nik.index') }}";
                        }
                    } catch (err) {
                        console.error('Parsing error:', err);
                    }
                };

                xhr.onerror = function() {
                    Swal.fire('Error', 'Submission failed.', 'error');
                    progressContainer.style.display = 'none';
                };

                xhr.send(new FormData(confirmForm));
            });
        });
    </script>
@endsection
