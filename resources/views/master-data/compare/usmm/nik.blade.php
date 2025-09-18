{{-- filepath: c:\Kerja\Project\2024\05. User Access Management\UserAccessManagement\resources\views\master-data\compare\usmm_nik.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">USMM Compare (NIK Users)</h4>

        <form id="filterFormNik" class="row g-2 mb-3">
            <div class="col-sm-3">
                <input class="form-control form-control-sm" name="company" placeholder="Filter company">
            </div>
            <div class="col-sm-3">
                <input class="form-control form-control-sm" name="search" placeholder="Search user / name / dept">
            </div>
            <div class="col-sm-2 d-grid">
                <button class="btn btn-sm btn-primary">Apply</button>
            </div>
            <div class="col-sm-2 d-grid">
                <button type="button" id="btnResetNik" class="btn btn-sm btn-secondary">Reset</button>
            </div>
        </form>

        <div class="row g-3 mb-3">
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2"><small>Total Local</small>
                        <div id="nikStatTotalLocal" class="fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2"><small>Total Middle</small>
                        <div id="nikStatTotalMdb" class="fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2"><small>Only Local</small>
                        <div id="nikStatOnlyLocal" class="fw-bold text-primary">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2"><small>Only Middle</small>
                        <div id="nikStatOnlyMdb" class="fw-bold text-warning">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-bg-light">
                    <div class="card-body p-2"><small>In Both</small>
                        <div id="nikStatInBoth" class="fw-bold text-success">0</div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nikAll">All
                    Rows</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nikLocalOnly">Local
                    Only</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nikMiddleOnly">Middle
                    Only</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nikBoth">In Both</button>
            </li>
        </ul>

        <div class="tab-content p-2 border border-top-0">
            <div class="tab-pane fade show active" id="nikAll">
                <table id="nikTblAll" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>In Local</th>
                            <th>In Middle</th>
                            <th>Local Type</th>
                            <th>Middle Type</th>
                            <th>License</th>
                            <th>Diff</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="tab-pane fade" id="nikLocalOnly">
                <table id="nikTblLocalOnly" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>User Type</th>
                            <th>License</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="tab-pane fade" id="nikMiddleOnly">
                <table id="nikTblMiddleOnly" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>User Type</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="tab-pane fade" id="nikBoth">
                <table id="nikTblBoth" class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Local Type</th>
                            <th>Middle Type</th>
                            <th>Diff?</th>
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
            const urlData = '{{ url('compare/usmm/nik/data') }}';

            let dtAll = $('#nikTblAll').DataTable({
                pageLength: 25
            });
            let dtLocal = $('#nikTblLocalOnly').DataTable({
                pageLength: 25
            });
            let dtMdb = $('#nikTblMiddleOnly').DataTable({
                pageLength: 25
            });
            let dtBoth = $('#nikTblBoth').DataTable({
                pageLength: 25
            });

            function loadNik() {
                const params = new URLSearchParams(new FormData(document.getElementById('filterFormNik')))
                    .toString();
                fetch(urlData + '?' + params)
                    .then(r => r.json())
                    .then(res => {
                        const rows = res.data || [];
                        const s = res.summary || {};
                        document.getElementById('nikStatTotalLocal').textContent = s.total_local ?? 0;
                        document.getElementById('nikStatTotalMdb').textContent = s.total_mdb ?? 0;
                        document.getElementById('nikStatOnlyLocal').textContent = s.only_in_local ?? 0;
                        document.getElementById('nikStatOnlyMdb').textContent = s.only_in_mdb ?? 0;
                        document.getElementById('nikStatInBoth').textContent = s.in_both ?? 0;

                        dtAll.clear();
                        dtLocal.clear();
                        dtMdb.clear();
                        dtBoth.clear();

                        rows.forEach(r => {
                            const diff = Object.keys(r.diffs || {}).length > 0 ? 'YES' : '';
                            dtAll.row.add([
                                r.key,
                                r.in_local ? 'Y' : '',
                                r.in_mdb ? 'Y' : '',
                                r.local?.user_type ?? '',
                                r.mdb?.user_type ?? '',
                                r.local?.license_type ?? '',
                                diff
                            ]);
                            if (r.in_local && !r.in_mdb) {
                                dtLocal.row.add([
                                    r.key,
                                    r.local?.user_type ?? '',
                                    r.local?.license_type ?? ''
                                ]);
                            } else if (!r.in_local && r.in_mdb) {
                                dtMdb.row.add([
                                    r.key,
                                    r.mdb?.user_type ?? '',
                                    r.mdb?.department ?? ''
                                ]);
                            } else if (r.in_local && r.in_mdb) {
                                dtBoth.row.add([
                                    r.key,
                                    r.local?.user_type ?? '',
                                    r.mdb?.user_type ?? '',
                                    diff
                                ]);
                            }
                        });

                        dtAll.draw();
                        dtLocal.draw();
                        dtMdb.draw();
                        dtBoth.draw();
                    });
            }

            document.getElementById('filterFormNik').addEventListener('submit', e => {
                e.preventDefault();
                loadNik();
            });
            document.getElementById('btnResetNik').addEventListener('click', () => {
                document.querySelectorAll('#filterFormNik input').forEach(i => i.value = '');
                loadNik();
            });

            loadNik();
        });
    </script>
@endsection
