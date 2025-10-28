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

        /* Column search styling */
        .filters input {
            font-size: 0.875rem;
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
                <h2>Composite Role - Single Role Mapping</h2>
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

                <table id="compositeSingleTable" class="table table-sm table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="15%">Company</th>
                            <th width="20%">Composite Role</th>
                            <th width="20%">Single Role</th>
                            <th>Description</th>
                            <th width="10%">Actions</th>
                        </tr>
                        <tr>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Company"></th>
                            <th><input type="text" class="form-control form-control-sm"
                                    placeholder="Cari Composite Role"></th>
                            <th><input type="text" class="form-control form-control-sm column-search-single-role"
                                    placeholder="Cari Single Role"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Cari Description">
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pre-fill single role search if passed from query param
            const urlParams = new URLSearchParams(window.location.search);
            const searchSingleRole = urlParams.get('search_single_role');

            const table = $('#compositeSingleTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: '{{ route('composite-single.datatable') }}',
                    data: function(d) {
                        @if (request('company_id'))
                            d.company_id = '{{ request('company_id') }}';
                        @endif
                    }
                },
                columns: [{
                        data: 'company',
                        name: 'company',
                        className: 'comp-col'
                    },
                    {
                        data: 'composite_role',
                        name: 'composite_role',
                        className: 'comp-col'
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
                    },
                ],
                order: [
                    [0, 'asc'],
                    [1, 'asc']
                ],
                orderCellsTop: true,
                drawCallback: function() {
                    applyRowSpans(this.api());
                },
                initComplete: function() {
                    const api = this.api();

                    // Bind column filters
                    $('#compositeSingleTable thead tr:eq(1) th').each(function(i) {
                        const $input = $(this).find('input');
                        if ($input.length) {
                            $input.on('keyup change clear', function() {
                                const val = this.value;
                                if (api.column(i).search() !== val) {
                                    api.column(i).search(val).draw();
                                }
                            });
                        }
                    });

                    // Auto-search single role column if query param is set
                    if (searchSingleRole) {
                        const singleRoleInput = $('.column-search-single-role');
                        singleRoleInput.val(searchSingleRole);
                        api.column(2).search(searchSingleRole).draw();
                    }
                }
            });

            function applyRowSpans(api) {
                groupColumn(api, 0);
                groupColumn(api, 1);
                groupColumn(api, 4);
            }

            function groupColumn(api, colIndex) {
                let lastKey = null;
                let rowspanCell = null;
                let spanCount = 0;

                api.rows({
                    page: 'current'
                }).every(function(rowIdx) {
                    const data = this.data();
                    const key = data.group_key;
                    const cell = $(api.cell(rowIdx, colIndex).node());

                    if (colIndex === 0 || colIndex === 1 || colIndex === 4) {
                        if (lastKey === key) {
                            cell.addClass('_grp-hidden');
                            spanCount++;
                            if (rowspanCell) rowspanCell.attr('rowspan', spanCount);
                        } else {
                            lastKey = key;
                            rowspanCell = cell;
                            spanCount = 1;
                            cell.removeClass('_grp-hidden').attr('rowspan', 1);
                        }
                    }
                });
            }
        });
    </script>
@endsection
