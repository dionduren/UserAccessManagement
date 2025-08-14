@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">🔍 Grouped Work Unit Mapping</h4>

        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <label>Periode</label>
                <select id="periode_id" class="form-select">
                    @foreach ($periodes as $p)
                        <option value="{{ $p->id }}">{{ $p->definisi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Company</label>
                <select id="company_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($companies->sortBy('company_code') as $c)
                        <option value="{{ $c->company_code }}">{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Kompartemen</label>
                <select id="kompartemen_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($kompartemens->sortBy('nama') as $k)
                        <option value="{{ $k->kompartemen_id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Departemen</label>
                <select id="departemen_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($departemens->sortBy('nama') as $d)
                        <option value="{{ $d->departemen_id }}">{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button id="load" class="btn btn-primary mb-3">🔄 Load</button>

        <div id="report-table"></div>
    </div>
@endsection

@section('scripts')
    <script>
        let table;

        $('#load').on('click', function() {
            const params = {
                periode_id: $('#periode_id').val(),
                company_id: $('#company_id').val(),
                kompartemen_id: $('#kompartemen_id').val(),
                departemen_id: $('#departemen_id').val(),
            };

            fetch(`{{ route('report.unit.groupedData') }}?` + new URLSearchParams(params))
                .then(res => res.json())
                .then(data => {
                    if (table) table.destroy();

                    table = new Tabulator("#report-table", {
                        data: data.data,
                        layout: "fitColumns",
                        groupBy: ["company", "kompartemen", "departemen", "job_role", "composite_role",
                            "single_role"
                        ],
                        groupHeader: function(value, count, data, group) {
                            let groupName = group.getField() === "company" ? "Perusahaan: " +
                                value :
                                group.getField() === "kompartemen" ? "Kompartemen: " + value :
                                group.getField() === "departemen" ? "Departemen: " + value :
                                group.getField() === "job_role" ? "Job Role: " + value :
                                group.getField() === "composite_role" ? "Composite Role: " + value :
                                group.getField() === "single_role" ? "Single Role: " + value +
                                " - " + data[0].single_role_desc :
                                "";
                            return `${groupName} (${count} items)`;
                        },
                        groupStartOpen: false,
                        columns: [
                            // {
                            //     title: "Composite Role",
                            //     field: "composite_role",
                            //     headerSort: false
                            // },
                            // {
                            //     title: "Single Role",
                            //     field: "single_role",
                            //     headerSort: false
                            // },
                            // {
                            //     title: "Single Role Desc",
                            //     field: "single_role_desc",
                            //     headerSort: false
                            // },
                            {
                                title: "TCode",
                                field: "tcode",
                                headerSort: false
                            },
                            // {
                            //     title: "SAP Module",
                            //     field: "sap_module",
                            //     headerSort: false
                            // },
                            {
                                title: "TCode Desc",
                                field: "tcode_desc",
                                headerSort: false
                            },
                        ],
                        responsiveLayout: "collapse",
                        movableColumns: true,
                        resizableColumns: true,
                        tooltips: true,
                        height: "700px",
                    });
                });
        });
    </script>
@endsection
