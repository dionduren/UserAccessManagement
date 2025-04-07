{{-- resources/views/report/unit_kerja/nested.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">🧩 Nested Work Unit Report</h3>

        <div class="row mb-3">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>📅 Periode</label>
                    <select id="periode_id" class="form-select select2" required>
                        @foreach (\App\Models\Periode::orderByDesc('id')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->definisi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>🏢 Company</label>
                    <select id="company_id" class="form-select select2">
                        <option value="">All</option>
                        @foreach (\App\Models\Company::select('id', 'name')->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>📁 Kompartemen</label>
                    <select id="kompartemen_id" class="form-select select2">
                        <option value="">All</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>📂 Departemen</label>
                    <select id="departemen_id" class="form-select select2">
                        <option value="">All</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-2 d-flex align-items-end">
                    <button id="load-data" class="btn btn-primary">🔄 Load Report</button>
                </div>
            </div>
        </div>

        <div id="main-table"></div>
    </div>
@endsection

@section('scripts')
    <script>
        $('.select2').select2();
        let mainTable;

        const fetchOptions = async (url, target, append = false) => {
            let res = await fetch(url);
            let data = await res.json();
            let html = '<option value="">All</option>';
            data.forEach(opt => {
                html += `<option value="${opt.id}">${opt.name}</option>`;
            });
            $(target).html(html);
        };

        $('#company_id').on('change', function() {
            const companyId = $(this).val();
            fetchOptions(`/api/cascade/kompartemen?company_id=${companyId}`, '#kompartemen_id');
            $('#departemen_id').html('<option value="">All</option>');
        });

        $('#kompartemen_id').on('change', function() {
            const kompId = $(this).val();
            fetchOptions(`/api/cascade/departemen?kompartemen_id=${kompId}`, '#departemen_id');
        });

        $('#load-data').on('click', function() {
            const params = {
                periode_id: $('#periode_id').val(),
                company_id: $('#company_id').val(),
                kompartemen_id: $('#kompartemen_id').val(),
                departemen_id: $('#departemen_id').val()
            };

            const query = new URLSearchParams(params).toString();
            fetch(`{{ route('report.unit.nestedData') }}?${query}`)
                .then(res => res.json())
                .then(({
                    data
                }) => {
                    if (mainTable) mainTable.destroy();
                    mainTable = new Tabulator("#main-table", {
                        data,
                        layout: "fitDataStretch",
                        columns: [{
                            title: "Company",
                            field: "company",
                            columns: [{
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
                                }
                            ],

                        }],
                        rowFormatter: function(row) {
                            let nested = row.getData().composite_roles;
                            if (!nested?.length) return;

                            let holder = document.createElement("div");
                            holder.style.margin = "1em";

                            new Tabulator(holder, {
                                layout: "fitColumns",
                                data: nested,
                                columns: [{
                                        title: "Composite Role",
                                        field: "name"
                                    },
                                    {
                                        title: "Single Roles",
                                        field: "single_roles",
                                        formatter: function(cell) {
                                            const roles = cell
                                                .getValue();
                                            let html = `<ul>`;
                                            for (let sr of roles) {
                                                html +=
                                                    `<li><strong>${sr.name}</strong>: ${sr.deskripsi}<br>`;
                                                if (sr.tcodes?.length) {
                                                    html += `<ul>`;
                                                    for (let tc of sr
                                                            .tcodes) {
                                                        html +=
                                                            `<li>${tc.code} - ${tc.sap_module ?? '-'}: ${tc.deskripsi}</li>`;
                                                    }
                                                    html += `</ul>`;
                                                }
                                                html += `</li>`;
                                            }
                                            html += `</ul>`;
                                            return html;
                                        }
                                    }
                                ]
                            });

                            row.getElement().appendChild(holder);
                        }
                    });
                });
        });
    </script>
@endsection
