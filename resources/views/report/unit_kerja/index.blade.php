{{-- resources/views/report/nested/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">🔍 Nested Work Unit Mapping</h4>
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
                departemen_id: $('#departemen_id').val()
            };

            fetch(`{{ route('report.unit.nestedData') }}?` + new URLSearchParams(params))
                .then(res => res.json())
                .then(data => {
                    if (table) table.destroy();
                    table = new Tabulator("#report-table", {
                        layout: "fitDataStretch",
                        data: data.data,
                        columns: [{
                                title: "Company",
                                field: "company",
                                rowFormatter: function(row) {
                                    let users = row.getData().users;
                                    if (!users.length) return;

                                    let holder = document.createElement("div");
                                    holder.style.margin = "1em";

                                    new Tabulator(holder, {
                                        data: users,
                                        layout: "fitColumns",
                                        columns: [{
                                                title: "NIK",
                                                field: "nik"
                                            },
                                            {
                                                title: "Nama",
                                                field: "nama"
                                            },
                                            {
                                                title: "Job Role",
                                                field: "job_role"
                                            },
                                            {
                                                title: "Composite Role",
                                                field: "composite_role"
                                            },
                                        ]
                                    });

                                    row.getElement().appendChild(holder);
                                }
                            },
                            {
                                title: "Kompartemen",
                                field: "kompartemen"
                            },
                            {
                                title: "Departemen",
                                field: "departemen"
                            },
                        ]
                    });
                });
        });
    </script>
@endsection
