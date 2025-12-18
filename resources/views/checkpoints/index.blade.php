@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Checkpoint Progress</h4>
                <div class="d-flex gap-2">
                    <select id="periodeFilter" class="form-select form-select-sm" style="width: 200px;">
                        @foreach ($periodes as $p)
                            <option value="{{ $p->id }}" @selected($p->id == $selectedPeriode)>
                                {{ $p->definisi }}
                            </option>
                        @endforeach
                    </select>
                    <form method="POST"
                        action="{{ Route::currentRouteName() === 'checkpoints.index_old' ? route('checkpoints.refresh_old') : route('checkpoints.refresh') }}"
                        class="d-inline">
                        @csrf
                        <input type="hidden" name="periode_id" value="{{ $selectedPeriode }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Progress
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive table-sticky-header">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light align-top">
                            <tr>
                                <th class="w-25 sticky-col">Step</th>
                                @foreach ($companies as $company)
                                    <th class="text-center company-col company-header-col"
                                        width="{{ 80 / $companies->count() }}%">
                                        {{ $company->nama }}<br>
                                        <small class="text-muted">({{ $company->company_code }})</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($steps as $stepKey => $stepLabel)
                                <tr>
                                    <td class="fw-semibold align-top sticky-col">{!! clean($stepLabel) !!}</td>
                                    @foreach ($companies as $company)
                                        @php
                                            $cell = $matrix[$stepKey][$company->company_code] ?? [
                                                'status' => 'pending',
                                                'payload' => null,
                                            ];
                                            $status = $cell['status'];
                                            $payload = $cell['payload'] ?? [];
                                            $summary = $payload['summary'] ?? '';
                                            $duplicates = $payload['duplicates'] ?? null;

                                            $badgeClass = match ($status) {
                                                'completed' => 'bg-success',
                                                'in_progress' => 'bg-warning text-dark',
                                                'warning' => 'bg-warning text-dark', // ✅ Warning for duplicates
                                                'failed' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };

                                            $statusText = match ($status) {
                                                'completed' => '✓ Completed',
                                                'in_progress' => '⚠ In Progress',
                                                'warning' => '⚠ Warning',
                                                'failed' => '✗ Failed',
                                                default => '○ Pending',
                                            };
                                        @endphp
                                        <td class="text-center align-top company-col">
                                            <span class="badge {{ $badgeClass }} mb-2">{{ $statusText }}</span>

                                            @if ($summary)
                                                <div class="small text-start">
                                                    {!! clean($summary) !!}
                                                </div>
                                            @endif

                                            {{-- ✅ Single duplicate button for step 7 --}}
                                            @if ($stepKey === 'job_role_composite' && $duplicates)
                                                @php
                                                    $dupCount = max(
                                                        (int) ($duplicates['job_roles_multiple_composites'] ?? 0),
                                                        (int) ($duplicates['composites_multiple_job_roles'] ?? 0),
                                                    );
                                                @endphp
                                                @if ($dupCount > 0)
                                                    <div class="mt-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-show-duplicates"
                                                            data-company="{{ $company->company_code }}"
                                                            data-title="JobRole - Composite Duplicates - {{ $company->nama }}">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            Duplicates ({{ $dupCount }})
                                                        </button>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3 small text-muted">
        *Status default: Pending (belum diproses), In_progress (sedang diproses), Completed (selesai), Failed
        (gagal).

        {{-- ✅ Duplicate Modal --}}
        <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="duplicateModalLabel">Duplicate Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table id="duplicateTable" class="table table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Job Role Name</th>
                                    <th>Company</th>
                                    <th>Composite Count</th>
                                    <th>Composite Roles</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('header-scripts')
        <style>
            /* Scroll container; keep header/first column sticky */
            .table-sticky-header {
                overflow-y: auto;
                overflow-x: auto;
                max-height: 70vh;
            }

            /* Sticky header */
            .table-sticky-header thead th {
                position: sticky;
                top: 0;
                z-index: 2;
                background-color: #f8f9fa;
            }

            /* Sticky first column (header + body) */
            .table-sticky-header .sticky-col {
                position: sticky;
                left: 0;
                z-index: 3;
                background-color: #fff;
                box-shadow: 2px 0 0 rgba(0, 0, 0, .05);
            }

            /* Top-left cell */
            .table-sticky-header thead .sticky-col {
                z-index: 4;
                background-color: #f8f9fa;
                box-shadow: 2px 0 0 rgba(0, 0, 0, .05), 0 2px 0 rgba(0, 0, 0, .05);
            }

            /* Widen company columns */
            .table-sticky-header th.company-col,
            .table-sticky-header td.company-col {
                min-width: 150px;
                vertical-align: top;
            }

            /* Center + middle only for company header cells */
            .table-sticky-header thead th.company-header-col {
                text-align: center;
                vertical-align: middle !important;
            }
        </style>
    @endsection

    @section('scripts')
        <script>
            // Periode filter
            $('#periodeFilter').on('change', function() {
                window.location.href = '{{ route('checkpoints.index') }}?periode_id=' + this.value;
            });

            // ✅ Single duplicates modal trigger
            $('.btn-show-duplicates').on('click', function() {
                const companyCode = $(this).data('company');
                const title = $(this).data('title');

                $('#duplicateModalLabel').text(title);
                $('#duplicateModal').modal('show');

                if ($.fn.DataTable.isDataTable('#duplicateTable')) {
                    $('#duplicateTable').DataTable().destroy();
                    $('#duplicateTable tbody').empty();
                }

                // Always use one endpoint (data is the same)
                let url = '{{ route('checkpoints.duplicates.jobRolesMultipleComposites') }}';
                url += '?company_code=' + encodeURIComponent(companyCode);

                $('#duplicateTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: url,
                        dataSrc: ''
                    },
                    columns: [{
                            data: null,
                            render: (d, t, r, m) => m.row + 1
                        },
                        {
                            data: 'nama'
                        },
                        {
                            data: 'company.nama',
                            defaultContent: '-'
                        },
                        {
                            data: 'composite_count',
                            className: 'text-center'
                        },
                        {
                            data: 'composite_roles',
                            render: (data) => {
                                if (!data) return '-';
                                const roles = data.split(',').map(r => r.trim());
                                return '<ol class="mb-0 ps-3">' +
                                    roles.map(r => `<li><small>${r}</small></li>`).join('') +
                                    '</ol>';
                            }
                        }
                    ],
                    order: [
                        [3, 'desc']
                    ]
                });
            });

            document.addEventListener('DOMContentLoaded', () => {
                const select = document.getElementById('periode-select');
                const hidden = document.querySelector('#refresh-form input[name="periode_id"]');
                if (select && hidden) {
                    select.addEventListener('change', () => hidden.value = select.value);
                }
                const container = document.querySelector('.table-sticky-header');

                function adjustMaxHeight() {
                    if (!container) return;
                    const rect = container.getBoundingClientRect();
                    const bottomPadding = 24;
                    const max = window.innerHeight - rect.top - bottomPadding;
                    container.style.maxHeight = (max > 200 ? max : 200) + 'px';
                }
                adjustMaxHeight();
                window.addEventListener('resize', adjustMaxHeight);
            });
        </script>
    @endsection
