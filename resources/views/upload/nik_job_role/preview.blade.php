@extends('layouts.app')

@section('header-scripts')
    <!-- Tabulator CSS & JS -->
    <link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/6.3.0/css/tabulator_semanticui.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>
@endsection

@section('content')
    <div class="container-fluid">
        <h1>Preview NIK - Job Role</h1>

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

        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <div id="NIKJobRoleTable"></div>

        <button id="submit-all" class="btn btn-primary mt-3">Submit All</button>

        <form id="confirm-form" action="{{ route('nik_job_role.upload.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success mt-3">Confirm Import</button>
            <a href="{{ route('nik_job_role.upload.form') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>

    </div>
@endsection

@section('scripts')
    <script>
        const jobRoles = {!! json_encode(
            \App\Models\JobRole::select('id', 'nama_jabatan', 'departemen_id', 'kompartemen_id')->with('departemen:id,name')->with('kompartemen:id,name')->get(),
        ) !!};

        $(function() {
            const jobRoleOptions = jobRoles.map(role => ({
                value: role.id,
                label: `${role.nama_jabatan} (${(role.kompartemen ? "Kompartemen " + role.kompartemen.name : 'NullKompartemen')} - ${(role.departemen ? "Departemen " + role.departemen.name : 'NullDepartemen')})`
            }));


            const table = new Tabulator("#NIKJobRoleTable", {
                layout: "fitColumns",
                ajaxURL: "{{ route('nik_job_role.upload.preview_data') }}",
                ajaxResponse: function(url, params, response) {
                    return response.data || response;
                },
                pagination: "local",
                paginationSize: 10,
                columns: [{
                        title: "Periode",
                        field: "periode",
                        editor: "input",
                        cellEdited: styleEditable
                    },
                    {
                        title: "NIK",
                        field: "nik",
                        editor: "input",
                        cellEdited: styleEditable
                    },
                    {
                        title: "Job Role Suggested",
                        field: "job_role",
                        editable: false,
                        cellClick: function() {},
                        formatter: readOnlyFormatter
                    },
                    {
                        title: "Job Role Selected",
                        field: "job_role_id",
                        editor: "list",
                        editorParams: {
                            values: jobRoleOptions,
                            multiselect: false,
                            autocomplete: true,
                            sort: "asc",
                            placeholderLoading: "Loading List...",
                            placeholderEmpty: "No Results Found",
                            listOnEmpty: true,
                        },
                        cellEdited: styleEditable
                    },
                    {
                        title: "Actions",
                        formatter: function(cell) {
                            return '<button class="btn btn-success btn-sm submit-row">Submit Row</button>';
                        },
                        width: 120,
                        hozAlign: "center",
                        cellClick: function(e, cell) {
                            const rowData = cell.getRow().getData();
                            sendSingleRow(rowData);
                        }
                    }
                ],
                rowFormatter: function(row) {
                    row.getElement().style.transition = "all 0.3s ease";
                }
            });

            function styleEditable(cell) {
                if (cell.isEdited()) {
                    cell.getElement().style.backgroundColor = "#f0f9ff";
                } else {
                    cell.getElement().style.backgroundColor = "fffff";
                }
            }

            function readOnlyFormatter(cell) {
                cell.getElement().style.backgroundColor = "#e9ecef";
                return cell.getValue();
            }

            function sendSingleRow(row) {
                $.ajax({
                    url: '{{ route('nik_job_role.upload.submitSingle') }}',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        periode: row.periode,
                        nik: row.nik,
                        job_role_id: row.job_role_id
                    }),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert('Row submitted: ' + response.message);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Unknown error';
                        alert('Error: ' + msg);
                    }
                });
            }

            $('#submit-all').on('click', function() {
                const allData = table.getData();
                $.ajax({
                    url: '{{ route('nik_job_role.upload.submitAll') }}',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(allData),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endsection
