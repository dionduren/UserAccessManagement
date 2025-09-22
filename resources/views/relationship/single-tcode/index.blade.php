@extends('layouts.app')

@section('header-scripts')
    <style>
        table.table-sm td,
        table.table-sm th {
            padding: .4rem .5rem;
            vertical-align: top !important;
        }

        td._grp-hidden {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $m)
                        <li>{{ $m }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="mb-3">Single Role - Tcode Mapping</h2>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                    <a href="{{ route('single-tcode.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg"></i> Create
                    </a>
                </div>

                <div class="table-responsive">
                    <table id="singleRoleTcodeTable" class="table table-sm table-bordered w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">Single Role</th>
                                <th width="20%">Tcode</th>
                                <th>Description</th>
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
            const table = $('#singleRoleTcodeTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: '{{ route('single-tcode.datatable') }}',
                },
                columns: [{
                        data: 'single_role',
                        name: 'single_role',
                        className: 'single-col'
                    },
                    {
                        data: 'tcode',
                        name: 'tcode'
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
                    [0, 'asc']
                ],
                drawCallback: function() {
                    applyRowSpans(this.api());
                }
            });

            function applyRowSpans(api) {
                // Group single_role (col 0) and actions (col 3)
                groupColumn(api, 0);
                groupColumn(api, 3);
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

                    if (colIndex === 0 || colIndex === 3) {
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
