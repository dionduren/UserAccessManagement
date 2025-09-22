@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">UAM Synchronization Console</h4>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-12">
                        <h6 class="text-muted">Options</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <div>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" id="overwrite_single">
                                    <span class="form-check-label">Overwrite Single Role desc</span>
                                </label>
                            </div>
                            <div>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" id="overwrite_composite">
                                    <span class="form-check-label">Overwrite Composite Role desc</span>
                                </label>
                            </div>
                            <div>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" id="all_patterns">
                                    <span class="form-check-label">All patterns (skip sapPattern)</span>
                                </label>
                            </div>
                            <div>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" id="full_single_tcode">
                                    <span class="form-check-label">Full refresh SingleRole–Tcode pivot</span>
                                </label>
                            </div>
                            <div>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" id="full_composite_single">
                                    <span class="form-check-label">Full refresh Composite–Single pivot</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <h6 class="text-muted mt-2">Individual Sync</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary" data-action="tcodes">Sync Tcodes (Full)</button>
                            <button class="btn btn-sm btn-outline-primary" data-action="single_roles">Sync Single
                                Roles</button>
                            <button class="btn btn-sm btn-outline-primary" data-action="composite_roles">Sync Composite
                                Roles</button>
                            <button class="btn btn-sm btn-outline-primary" data-action="ao">Sync AO</button>
                            <button class="btn btn-sm btn-outline-primary" data-action="single_role_tcodes">Sync SingleRole
                                - Tcodes</button>
                            <button class="btn btn-sm btn-outline-primary" data-action="composite_role_single_roles">Sync
                                CompositeRole - SingleRole</button>
                        </div>
                    </div>

                    <div class="col-12">
                        <h6 class="text-muted mt-2">Batch</h6>
                        <button class="btn btn-success" id="btn-sync-all">Sync ALL</button>
                    </div>

                    <div class="col-12">
                        <h6 class="text-muted mt-3 mb-1">Result</h6>
                        <pre id="result" class="bg-dark text-light p-3 rounded small" style="min-height:220px; white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const resultEl = document.getElementById('result');

        function logResult(obj) {
            resultEl.textContent = JSON.stringify(obj, null, 2);
        }

        function endpoint(action) {
            switch (action) {
                case 'tcodes':
                    return "{{ route('import.uam.tcodes') }}";
                case 'single_roles':
                    return "{{ route('import.uam.single_roles') }}";
                case 'composite_roles':
                    return "{{ route('import.uam.composite_roles') }}";
                case 'ao':
                    return "{{ route('import.uam.composite_ao') }}";
                case 'single_role_tcodes':
                    return "{{ route('import.uam.single_role_tcodes') }}";
                case 'composite_role_single_roles':
                    return "{{ route('import.uam.composite_role_single_roles') }}";
            }
        }

        async function post(url, payload = {}) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            return res.json();
        }

        async function runSingle(action) {
            const opts = collectOptions();
            Swal.showLoading();
            try {
                const url = endpoint(action);
                const extra = buildPayload(action, opts);
                const data = await post(url, extra);
                Swal.close();
                Swal.fire('Done', data.message || 'Completed', 'success');
                logResult(data);
            } catch (e) {
                Swal.close();
                Swal.fire('Error', 'Request failed', 'error');
            }
        }

        function collectOptions() {
            return {
                overwrite_single: document.getElementById('overwrite_single').checked,
                overwrite_composite: document.getElementById('overwrite_composite').checked,
                all_patterns: document.getElementById('all_patterns').checked,
                full_single_tcode: document.getElementById('full_single_tcode').checked,
                full_composite_single: document.getElementById('full_composite_single').checked,
            };
        }

        function buildPayload(action, opts) {
            switch (action) {
                case 'single_roles':
                    return {
                        overwrite: opts.overwrite_single, all: opts.all_patterns
                    };
                case 'composite_roles':
                    return {
                        overwrite: opts.overwrite_composite, all: opts.all_patterns
                    };
                case 'single_role_tcodes':
                    return {
                        full_refresh: opts.full_single_tcode
                    };
                case 'composite_role_single_roles':
                    return {
                        full_refresh: opts.full_composite_single
                    };
                default:
                    return {};
            }
        }

        document.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', () => runSingle(btn.dataset.action));
        });

        document.getElementById('btn-sync-all').addEventListener('click', async () => {
            const opts = collectOptions();
            Swal.fire({
                title: 'Sync ALL?',
                text: 'This will run every sync sequentially (Tcodes full refresh included). Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then(async r => {
                if (!r.isConfirmed) return;
                Swal.showLoading();
                try {
                    const data = await post("{{ route('import.uam.sync') }}", opts);
                    Swal.close();
                    Swal.fire('Done', 'All sync finished', 'success');
                    logResult(data);
                } catch (e) {
                    Swal.close();
                    Swal.fire('Error', 'Sync all failed', 'error');
                }
            });
        });
    </script>
@endsection
