@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Bulk Tcode Search</h2>
            <a href="{{ route('single-tcode.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Single Role - Tcode
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Search Single Roles by Tcodes</h5>
                <small class="text-muted">Find which Single Roles contain your Tcodes. Paste one Tcode per line.</small>
            </div>
            <div class="card-body">
                <form id="tcodeSearchForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="tcodeInput" class="form-label">
                                <i class="bi bi-list-ul"></i> Enter Tcodes (one per line)
                            </label>
                            <textarea id="tcodeInput" name="tcodes" class="form-control font-monospace" rows="15"
                                placeholder="Example:&#10;FB01&#10;FB02&#10;FB03&#10;ME21N&#10;VA01&#10;MIRO" required></textarea>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Total lines: <strong id="lineCount">0</strong>
                                </small>
                            </div>

                            {{-- Toggle for Company Single Roles --}}
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="includeCompanyToggle"
                                    name="include_company" value="1">
                                <label class="form-check-label" for="includeCompanyToggle">
                                    <i class="bi bi-building"></i> <strong>Include Company Single Roles</strong>
                                    <br>
                                    <small class="text-muted">
                                        (e.g., ZS-A000-..., ZS-B000-..., ZS-C000-...)
                                    </small>
                                </label>
                            </div>

                            {{-- ✅ NEW: Module Filter Input --}}
                            <div class="mt-3">
                                <label for="moduleInput" class="form-label">
                                    <i class="bi bi-filter"></i> <strong>Filter by Modules (optional)</strong>
                                </label>
                                <input type="text" id="moduleInput" name="modules" class="form-control"
                                    placeholder="e.g., QM, PP, MD, MM (comma or space separated)">
                                <small class="text-muted">
                                    Leave empty to show all modules. Example: <code>QM PP MD</code> or <code>QM, PP,
                                        MD</code>
                                </small>
                                <div id="modulePreview" class="mt-2" style="min-height: 30px;"></div>
                            </div>

                            {{-- ✅ NEW: Process Area Filter --}}
                            <div class="mt-3">
                                <label for="processAreaInput" class="form-label">
                                    <i class="bi bi-diagram-3"></i> <strong>Filter by Process Area (optional)</strong>
                                </label>
                                <input type="text" id="processAreaInput" name="process_areas" class="form-control"
                                    placeholder="e.g., MD, PR, INV (comma or space separated)">
                                <small class="text-muted">
                                    Leave empty to show all process areas. Example: <code>MD PR INV</code>
                                </small>
                                <div id="processAreaPreview" class="mt-2" style="min-height: 30px;"></div>
                            </div>

                            {{-- ✅ NEW: Object Filter --}}
                            <div class="mt-3">
                                <label for="objectInput" class="form-label">
                                    <i class="bi bi-box"></i> <strong>Filter by Object (optional)</strong>
                                </label>
                                <input type="text" id="objectInput" name="objects" class="form-control"
                                    placeholder="e.g., MAT, PO, INV (comma or space separated)">
                                <small class="text-muted">
                                    Leave empty to show all objects. Example: <code>MAT PO INV</code>
                                </small>
                                <div id="objectPreview" class="mt-2" style="min-height: 30px;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-eye"></i> Live Preview
                            </label>
                            <div id="tcodePreview" class="border rounded p-3 bg-light"
                                style="min-height: 400px; max-height: 500px; overflow-y: auto;">
                                <span class="text-muted">Tcodes will appear here as you type...</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i> Search Single Roles
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="clearBtn">
                            <i class="bi bi-x-circle"></i> Clear All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Container -->
        <div id="resultsContainer" class="mt-4" style="display: none;">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <i class="bi bi-check2-circle"></i> Search Results
                            </h5>
                            <small>
                                Found <strong id="resultCount">0</strong> Single Role(s) |
                                Matched <strong id="foundCount">0</strong>/<strong id="inputCount">0</strong> Tcodes
                            </small>
                            <br>
                            <small id="filterInfo" class="badge bg-light text-dark mt-1"></small>
                            <small id="moduleFilterInfo" class="badge bg-info text-dark mt-1 ms-1"></small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Not Found Tcodes Alert -->
                    <div id="notFoundAlert" style="display: none;" class="alert alert-warning">
                        <strong><i class="bi bi-exclamation-triangle"></i> Tcodes not found in any Single Role:</strong>
                        <div id="notFoundList" class="mt-2"></div>
                    </div>

                    <!-- Excluded Info Alert -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Excluded from results:</strong> ZS-ALL-PIHC-TI
                        <span id="companyExcludeInfo"></span>
                    </div>

                    <div class="table-responsive">
                        <table id="resultsTable" class="table table-bordered table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%" class="text-center">No</th>
                                    <th width="20%">Single Role</th>
                                    <th width="25%">Description</th>
                                    <th width="8%" class="text-center">
                                        <i class="bi bi-check-circle text-success"></i> Matched
                                    </th>
                                    <th width="8%" class="text-center">
                                        <i class="bi bi-exclamation-circle text-warning"></i> Others
                                    </th>
                                    <th width="8%" class="text-center">Total</th>
                                    <th width="28%">Tcodes</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tcode-badge {
            display: inline-block;
            margin: 2px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .tcode-matched {
            background-color: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }

        .tcode-unmatched {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }

        .preview-badge {
            display: inline-block;
            margin: 3px;
            padding: 5px 12px;
            background: #0d6efd;
            color: white;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .module-badge {
            display: inline-block;
            margin: 3px;
            padding: 5px 12px;
            background: #6f42c1;
            color: white;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .process-area-badge {
            display: inline-block;
            margin: 3px;
            padding: 5px 12px;
            background: #fd7e14;
            color: white;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .object-badge {
            display: inline-block;
            margin: 3px;
            padding: 5px 12px;
            background: #20c997;
            color: white;
            border-radius: 4px;
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Live preview of Tcode input
            $('#tcodeInput').on('input', function() {
                const lines = $(this).val()
                    .split(/\r\n|\r|\n/)
                    .map(l => l.trim())
                    .filter(l => l.length > 0);

                $('#lineCount').text(lines.length);

                if (lines.length === 0) {
                    $('#tcodePreview').html(
                        '<span class="text-muted">Tcodes will appear here as you type...</span>');
                    return;
                }

                const preview = lines.map(l => `<span class="preview-badge">${l.toUpperCase()}</span>`)
                    .join('');
                $('#tcodePreview').html(preview);
            });

            // ✅ Live preview of Module input
            $('#moduleInput').on('input', function() {
                const modules = $(this).val()
                    .split(/[\s,]+/)
                    .map(m => m.trim().toUpperCase())
                    .filter(m => m.length > 0);

                if (modules.length === 0) {
                    $('#modulePreview').html('');
                    return;
                }

                const preview = modules.map(m => `<span class="module-badge">${m}</span>`).join('');
                $('#modulePreview').html('<small class="text-muted">Filter modules:</small> ' + preview);
            });

            // ✅ Live preview of Process Area input
            $('#processAreaInput').on('input', function() {
                const processAreas = $(this).val()
                    .split(/[\s,]+/)
                    .map(p => p.trim().toUpperCase())
                    .filter(p => p.length > 0);

                if (processAreas.length === 0) {
                    $('#processAreaPreview').html('');
                    return;
                }

                const preview = processAreas.map(p => `<span class="process-area-badge">${p}</span>`).join(
                    '');
                $('#processAreaPreview').html('<small class="text-muted">Filter process areas:</small> ' +
                    preview);
            });

            // ✅ Live preview of Object input
            $('#objectInput').on('input', function() {
                const objects = $(this).val()
                    .split(/[\s,]+/)
                    .map(o => o.trim().toUpperCase())
                    .filter(o => o.length > 0);

                if (objects.length === 0) {
                    $('#objectPreview').html('');
                    return;
                }

                const preview = objects.map(o => `<span class="object-badge">${o}</span>`).join('');
                $('#objectPreview').html('<small class="text-muted">Filter objects:</small> ' + preview);
            });

            // Clear button
            $('#clearBtn').on('click', function() {
                $('#tcodeInput').val('');
                $('#moduleInput').val('');
                $('#processAreaInput').val(''); // ✅ Clear process area
                $('#objectInput').val(''); // ✅ Clear object
                $('#lineCount').text('0');
                $('#tcodePreview').html(
                    '<span class="text-muted">Tcodes will appear here as you type...</span>');
                $('#modulePreview').html('');
                $('#processAreaPreview').html(''); // ✅ Clear preview
                $('#objectPreview').html(''); // ✅ Clear preview
                $('#resultsContainer').hide();
                $('#includeCompanyToggle').prop('checked', false);
            });

            // Search form submit
            $('#tcodeSearchForm').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: '{{ route('single-tcode.bulk.results') }}',
                    method: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('#resultsContainer').hide();
                        Swal.fire({
                            title: 'Searching...',
                            text: 'Finding Single Roles with your Tcodes',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },
                    success: function(response) {
                        Swal.close();

                        if (response.status === 'ok') {
                            displayResults(response);
                        } else {
                            Swal.fire('Error', response.message || 'Search failed', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        const msg = xhr.responseJSON?.message || 'Search request failed';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            function displayResults(data) {
                $('#inputCount').text(data.total_input);
                $('#foundCount').text(data.found_tcodes);
                $('#resultCount').text(data.single_roles_found);

                // Update filter info badge
                if (data.include_company) {
                    $('#filterInfo').html('<i class="bi bi-building"></i> Including Company Single Roles')
                        .removeClass('bg-warning').addClass('bg-success');
                    $('#companyExcludeInfo').html('');
                } else {
                    $('#filterInfo').html('<i class="bi bi-funnel"></i> Excluding Company Single Roles')
                        .removeClass('bg-success').addClass('bg-warning text-dark');
                    $('#companyExcludeInfo').html(' | Company patterns excluded (ZS-XXXX-...)');
                }

                // ✅ Update module filter info
                if (data.module_filter && data.module_filter.length > 0) {
                    const moduleBadges = data.module_filter.map(m =>
                        `<span class="badge bg-purple me-1">${m}</span>`).join('');
                    $('#moduleFilterInfo').html('<i class="bi bi-funnel-fill"></i> Modules: ' + moduleBadges)
                        .show();
                } else {
                    $('#moduleFilterInfo').html('<i class="bi bi-list"></i> All Modules').show();
                }

                // ✅ Update process area filter info
                let processAreaInfo = '';
                if (data.process_area_filter && data.process_area_filter.length > 0) {
                    const paBadges = data.process_area_filter.map(p =>
                        `<span class="badge bg-orange me-1">${p}</span>`).join('');
                    processAreaInfo =
                        `<small class="badge bg-info text-dark mt-1 ms-1"><i class="bi bi-diagram-3"></i> Process Areas: ${paBadges}</small>`;
                }

                // ✅ Update object filter info
                let objectInfo = '';
                if (data.object_filter && data.object_filter.length > 0) {
                    const objBadges = data.object_filter.map(o =>
                        `<span class="badge bg-teal me-1">${o}</span>`).join('');
                    objectInfo =
                        `<small class="badge bg-info text-dark mt-1 ms-1"><i class="bi bi-box"></i> Objects: ${objBadges}</small>`;
                }

                // Insert after module filter info
                $('#moduleFilterInfo').after(processAreaInfo + objectInfo);

                // Show not found Tcodes
                if (data.not_found_tcodes.length > 0) {
                    const notFoundBadges = data.not_found_tcodes.map(t =>
                        `<span class="badge bg-danger me-1">${t}</span>`
                    ).join('');
                    $('#notFoundList').html(notFoundBadges);
                    $('#notFoundAlert').show();
                } else {
                    $('#notFoundAlert').hide();
                }

                if (data.results.length === 0) {
                    $('#resultsBody').html(
                        '<tr><td colspan="7" class="text-center text-muted py-4">No Single Roles found matching your Tcodes and filters</td></tr>'
                    );
                    $('#resultsContainer').show();
                    return;
                }

                let rows = '';
                data.results.forEach((sr, index) => {
                    const tcodeBadges = sr.tcodes.map(t => {
                        const badgeClass = t.matched ? 'tcode-matched' : 'tcode-unmatched';
                        const icon = t.matched ? '✓' : '';
                        const title = t.deskripsi || 'No description';
                        return `<span class="tcode-badge ${badgeClass}" title="${title}">
                    ${icon} ${t.code}
                </span>`;
                    }).join('');

                    rows += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${sr.nama}</strong></td>
                    <td><small>${sr.deskripsi || '-'}</small></td>
                    <td class="text-center">
                        <span class="badge bg-success">${sr.matched_count}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark">${sr.unmatched_count}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">${sr.total_tcodes}</span>
                    </td>
                    <td>${tcodeBadges}</td>
                </tr>
            `;
                });

                $('#resultsBody').html(rows);
                $('#resultsContainer').show();
            }
        });
    </script>
@endsection
