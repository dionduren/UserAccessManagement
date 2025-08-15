@extends('layouts.app')

@section('header-scripts')
    <style>
        table.table-sm td,
        table.table-sm th {
            padding: .4rem .5rem;
            vertical-align: top !important;
        }

        /* Hide cells we collapse for rowspan */
        td._grp-hidden {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- General Error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="mb-3">Composite Role - Single Role Mapping</h2>
            </div>
            <div class="card-body">

                <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                    @if ($userCompanyCode === 'A000')
                        <form id="filterForm" class="d-flex gap-2">
                            <div>
                                <label class="form-label mb-1 small">Company</label>
                                <select id="companyFilter" name="company_id" class="form-select form-select-sm">
                                    <option value="">-- All --</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->company_code }}" @selected($selectedCompany === $c->company_code)>
                                            {{ $c->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    @else
                        <span class="badge bg-secondary">
                            Company: {{ $companies->first()->nama }} ({{ $companies->first()->company_code }})
                        </span>
                    @endif

                    <a href="{{ route('composite-single.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg"></i> Create
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table id="compositeRolesTable" class="table table-sm table-bordered w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">Company</th>
                                <th width="20%">Composite Role</th>
                                <th width="20%">Single Role</th>
                                <th width="auto">Description</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#compositeRolesTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: '{{ route('composite-single.datatable') }}',
                    data: function(d) {
                        d.company_id = $('#companyFilter').val();
                    }
                },
                columns: [{
                        data: 'company',
                        name: 'company',
                        className: 'company-col'
                    },
                    {
                        data: 'composite_role',
                        name: 'composite_role',
                        className: 'composite-col'
                    },
                    {
                        data: 'single_role',
                        name: 'single_role'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'actions-col'
                    }
                ],
                // order: [
                //     [0, 'asc'],
                //     [1, 'asc'],
                //     [2, 'asc']
                // ],
                drawCallback: function(settings) {
                    applyRowSpans(this.api());
                }
            });

            $('#companyFilter').on('change', function() {
                table.ajax.reload();
            });

            function applyRowSpans(api) {
                // Columns to group: company (0), composite (1), actions (4). Actions grouped by composite only.
                groupColumn(api, 0, 1); // group by composite when same composite id
                groupColumn(api, 1, 1);
                groupColumn(api, 4, 1);
            }

            function groupColumn(api, colIndex, keyColIndex) {
                let lastKey = null;
                let rowspanCell = null;
                let spanCount = 0;

                api.rows({
                    page: 'current'
                }).every(function(rowIdx) {
                    const data = this.data();
                    const key = data.group_key; // composite role id
                    const cell = $(api.cell(rowIdx, colIndex).node());

                    if (colIndex === 0 || colIndex === 1) {
                        // For company & composite, group by composite id
                    }
                    if (lastKey === key) {
                        // hide
                        cell.addClass('_grp-hidden');
                        spanCount++;
                        if (rowspanCell) rowspanCell.attr('rowspan', spanCount);
                    } else {
                        // reset
                        lastKey = key;
                        rowspanCell = cell;
                        spanCount = 1;
                        cell.removeClass('_grp-hidden').attr('rowspan', 1);
                    }
                });
            }
        });
    </script>
@endsection
