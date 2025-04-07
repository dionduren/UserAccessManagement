@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">📋 Work Unit Report</h2>

        <form id="filter-form" class="row mb-3 g-3">
            <div class="col-md-3">
                <label>📅 Periode <span class="text-danger">*</span></label>
                <select name="periode_id" class="form-select select2" required>
                    <option value="">Select Periode</option>
                    @foreach ($periodes as $p)
                        <option value="{{ $p->id }}">{{ $p->definisi }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>🏢 Company</label>
                <select name="company_id" class="form-select select2">
                    <option value="">All</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>📁 Kompartemen</label>
                <select name="kompartemen_id" class="form-select select2">
                    <option value="">All</option>
                    @foreach ($kompartemens as $k)
                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>📂 Departemen</label>
                <select name="departemen_id" class="form-select select2">
                    <option value="">All</option>
                    @foreach ($departemens as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div id="report-table"></div>

        <div class="mt-3 d-flex justify-content-between">
            <button id="reload" class="btn btn-primary">🔄 Reload</button>
            <button id="download-xlsx" class="btn btn-success">⬇️ Download Excel</button>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('.select2').select2();

        const table = new Tabulator("#report-table", {
            layout: "fitDataStretch",
            ajaxURL: "{{ route('report.unit.data') }}",
            ajaxConfig: {
                method: "GET",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            ajaxParams: function() {
                return $('#filter-form').serializeArray().reduce((acc, curr) => {
                    acc[curr.name] = curr.value;
                    return acc;
                }, {});
            },
            autoLoad: false,
            pagination: "local",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            ajaxResponse: function(url, params, response) {
                return response.data;
            },
            columns: [{
                    title: "NIK",
                    field: "nik"
                },
                {
                    title: "Nama",
                    field: "nama"
                },
                {
                    title: "Company",
                    field: "company"
                },
                {
                    title: "Kompartemen",
                    field: "kompartemen"
                },
                {
                    title: "Departemen",
                    field: "departemen"
                },
                {
                    title: "Job Role",
                    field: "job_role"
                },
                {
                    title: "Composite Role",
                    field: "composite_roles"
                },
                {
                    title: "Single Role",
                    field: "single_roles",
                    formatter: "textarea"
                },
                {
                    title: "TCode",
                    field: "tcodes",
                    hozAlign: "center"
                },
                {
                    title: "TCode Count",
                    field: "tcode_count",
                    hozAlign: "center"
                },
            ]
        });

        $('#reload').on('click', function() {
            table.setData();
        });

        $('#download-xlsx').on('click', function() {
            table.download("xlsx", "WorkUnitReport.xlsx", {
                sheetName: "Report"
            });
        });
    </script>
@endsection
