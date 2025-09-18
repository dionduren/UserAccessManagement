{{-- filepath: c:\Kerja\Project\2024\05. User Access Management\UserAccessManagement\resources\views\master-data\compare\usmm_generic.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">USMM Compare (Generic Users)</h4>

        <form id="filterForm" class="row g-2 mb-3">
            <div class="col-sm-3">
                <input class="form-control form-control-sm" name="company" placeholder="Filter company">
            </div>
            <div class="col-sm-3">
                <input class="form-control form-control-sm" name="search" placeholder="Search (User / Name / Dept)">
            </div>
            <div class="col-sm-2 d-grid">
                <button class="btn btn-sm btn-primary">Apply</button>
            </div>
            <div class="col-sm-2 d-grid">
                <button type="button" id="btnReset" class="btn btn-sm btn-secondary">Reset</button>
            </div>
        </form>

        <div class="row g-3 mb-3">
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2">
                        <small>Total Local</small>
                        <div id="statTotalLocal" class="fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2">
                        <small>Total Middle</small>
                        <div id="statTotalMdb" class="fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2">
                        <small>Only Local</small>
                        <div id="statOnlyLocal" class="fw-bold text-primary">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2">
                        <small>Only Middle</small>
                        <div id="statOnlyMdb" class="fw-bold text-warning">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2">
                        <small>In Both</small>
                        <div id="statInBoth" class="fw-bold text-success">0</div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" id="cmpTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAll" type="button">All
                    Rows</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLocalOnly" type="button">Local
                    Only</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMiddleOnly" type="button">Middle
                    Only</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBoth" type="button">In Both</button>
            </li>
        </ul>

        <div class="tab-content p-2 border border-top-0">
            <div class="tab-pane fade show active" id="tabAll">
                <table id="tblAll" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>In Local</th>
                            <th>In Middle</th>
                            <th>Local Type</th>
                            <th>Middle Type</th>
                            <th>Diff</th>
                            <th>Diff Details</th> {{-- NEW --}}
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="tab-pane fade" id="tabLocalOnly">
                <table id="tblLocalOnly" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>User Type (Local)</th>
                            <th>Periode</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="tab-pane fade" id="tabMiddleOnly">
                <table id="tblMiddleOnly" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>User Type (Middle)</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="tab-pane fade" id="tabBoth">
                <table id="tblBoth" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Local Type</th>
                            <th>Middle Type</th>
                            <th>Diff?</th>
                            <th>Diff Details</th> {{-- NEW --}}
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const urlData = '{{ url('compare/usmm/generic/data') }}';

            let dtAll = $('#tblAll').DataTable({
                pageLength: 25
            });
            let dtLocal = $('#tblLocalOnly').DataTable({
                pageLength: 25
            });
            let dtMdb = $('#tblMiddleOnly').DataTable({
                pageLength: 25
            });
            let dtBoth = $('#tblBoth').DataTable({
                pageLength: 25
            });

            function buildDiffList(diffs) {
                const items = Object.values(diffs || {}).map(d => {
                    return `<li><strong>${d.local_field}</strong>: <span class="text-danger">${d.local ?? ''}</span> → <span class="text-success">${d.middle ?? ''}</span></li>`;
                });
                return items.length ? `<ul class="mb-0 small">${items.join('')}</ul>` : '';
            }

            function loadData() {
                const params = new URLSearchParams(new FormData(document.getElementById('filterForm'))).toString();
                fetch(urlData + '?' + params)
                    .then(r => r.json())
                    .then(res => {
                        const rows = res.data || [];
                        const summary = res.summary || {};

                        document.getElementById('statTotalLocal').textContent = summary.total_local ?? 0;
                        document.getElementById('statTotalMdb').textContent = summary.total_mdb ?? 0;
                        document.getElementById('statOnlyLocal').textContent = summary.only_in_local ?? 0;
                        document.getElementById('statOnlyMdb').textContent = summary.only_in_mdb ?? 0;
                        document.getElementById('statInBoth').textContent = summary.in_both ?? 0;

                        dtAll.clear();
                        dtLocal.clear();
                        dtMdb.clear();
                        dtBoth.clear();

                        rows.forEach(r => {
                            // NOTE: controller returns 'middle', not 'mdb'
                            const localUserType = r.local?.user_type ?? '';
                            const middleUserType = r.middle?.user_type ?? '';
                            const diffFlag = Object.keys(r.diffs || {}).length > 0 ? 'YES' : '';
                            const diffDetails = buildDiffList(r.diffs);

                            // All rows
                            dtAll.row.add([
                                r.key,
                                r.in_local ? 'Y' : '',
                                r.in_mdb ? 'Y' : '',
                                localUserType,
                                middleUserType,
                                diffFlag,
                                diffDetails
                            ]);

                            if (r.in_local && !r.in_mdb) {
                                dtLocal.row.add([
                                    r.key,
                                    localUserType,
                                    r.local?.periode_id ?? ''
                                ]);
                            } else if (!r.in_local && r.in_mdb) {
                                dtMdb.row.add([
                                    r.key,
                                    middleUserType,
                                    r.middle?.department ?? ''
                                ]);
                            } else if (r.in_local && r.in_mdb) {
                                dtBoth.row.add([
                                    r.key,
                                    localUserType,
                                    middleUserType,
                                    diffFlag,
                                    diffDetails
                                ]);
                            }
                        });

                        dtAll.draw();
                        dtLocal.draw();
                        dtMdb.draw();
                        dtBoth.draw();
                    });
            }

            document.getElementById('filterForm').addEventListener('submit', e => {
                e.preventDefault();
                loadData();
            });
            document.getElementById('btnReset').addEventListener('click', () => {
                document.querySelectorAll('#filterForm input').forEach(i => i.value = '');
                loadData();
            });

            loadData();
        });
    </script>
@endsection
