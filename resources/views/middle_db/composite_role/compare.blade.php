@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0 flex-grow-1">Compare Composite Role Content</h5>
                <form class="d-flex gap-2" method="GET" action="{{ route('middle_db.uam.composite_role.compare') }}">
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Composite Role Name"
                        value="{{ $name }}">
                    <button class="btn btn-primary btn-sm" type="submit">Compare</button>
                    @if ($name)
                        <a href="{{ route('middle_db.uam.composite_role.compare') }}"
                            class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </form>
            </div>
            <div class="card-body">
                @if ($name === '')
                    <p class="text-muted mb-0">Enter a composite role name to begin.</p>
                @else
                    <h6 class="mb-3">Composite Role: <span class="text-primary">{{ $name }}</span></h6>

                    @if (isset($metaRows) && count($metaRows))
                        <h6 class="mt-2 mb-2">Metadata Comparison</h6>
                        <table class="table table-sm table-bordered w-auto mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Attribute</th>
                                    <th>Local</th>
                                    <th>Middle DB (RAW)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($metaRows as $i => $row)
                                    @php
                                        // No highlight for metadata rows anymore
                                        $label = $row['label'];
                                        $rawVal = $row['raw'];

                                        // For Kompartemen & Departemen rows: render multiple values as <ul><li>id / name</li>...
                                        $needsList =
                                            Str::startsWith($label, 'Kompartemen') ||
                                            Str::startsWith($label, 'Departemen');
                                        $rawFormatted = e($rawVal);

                                        if ($needsList && $rawVal !== '-' && $rawVal !== '') {
                                            // Expected format: "id1, id2 / name1, name2"
                                            $parts = preg_split('#\s*/\s*#', $rawVal, 2);
                                            $idsPart = $parts[0] ?? '';
                                            $namesPart = $parts[1] ?? '';

                                            $ids = collect(explode(',', $idsPart))
                                                ->map(fn($v) => trim($v))
                                                ->filter(fn($v) => $v !== '')
                                                ->values();

                                            $names = collect(explode(',', $namesPart))
                                                ->map(fn($v) => trim($v))
                                                ->filter(fn($v) => $v !== '')
                                                ->values();

                                            // Build list items pairing by index
                                            if ($ids->count() > 1 || $names->count() > 1) {
                                                $items = [];
                                                $max = max($ids->count(), $names->count());
                                                for ($k = 0; $k < $max; $k++) {
                                                    $idVal = $ids->get($k, '-');
                                                    $nameVal = $names->get($k, '-');
                                                    $items[] = '<li>' . e($idVal) . ' / ' . e($nameVal) . '</li>';
                                                }
                                                $rawFormatted =
                                                    '<ul class="mb-0 ps-3">' . implode('', $items) . '</ul>';
                                            } else {
                                                // Single pair fallback keep original formatting
                                                $rawFormatted = e($rawVal);
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-end">{{ $i + 1 }}</td>
                                        <td>{{ $label }}</td>
                                        <td class="{{ $row['local'] === '-' ? 'text-muted' : '' }}">{{ $row['local'] }}</td>
                                        <td class="{{ $rawVal === '-' ? 'text-muted' : '' }}">
                                            {!! clean($rawFormatted) !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @php
                        // Reorder Single Role rows: differences (one side '-') first
                        $singleCollection = collect($singleRows);
                        $singleDiff = $singleCollection->filter(fn($r) => $r['local'] === '-' xor $r['raw'] === '-');
                        $singleSame = $singleCollection->reject(fn($r) => $r['local'] === '-' xor $r['raw'] === '-');
                        $singleOrdered = $singleDiff->concat($singleSame)->values();

                        // Reorder Tcode rows similarly
                        $tcodeCollection = collect($tcodeRows);
                        $tcodeDiff = $tcodeCollection->filter(fn($r) => $r['local'] === '-' xor $r['raw'] === '-');
                        $tcodeSame = $tcodeCollection->reject(fn($r) => $r['local'] === '-' xor $r['raw'] === '-');
                        $tcodeOrdered = $tcodeDiff->concat($tcodeSame)->values();
                    @endphp

                    @if (isset($assignedUsersLocal) && $assignedUsersLocal->count())
                        <h6 class="mt-4">Assigned Users - Local</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px">#</th>
                                        <th>NIK</th>
                                        <th>Employee Name</th>
                                        <th>Company</th>
                                        <th>Kompartemen</th>
                                        <th>Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($assignedUsersLocal as $i => $u)
                                        <tr>
                                            <td class="text-end">{{ $i + 1 }}</td>
                                            <td>{{ $u['nik'] }}</td>
                                            <td class="{{ $u['nama'] === '-' ? 'text-muted' : '' }}">
                                                {{ $u['nama'] }}</td>
                                            <td>{{ $u['company'] }}</td>
                                            <td>{{ $u['kompartemen'] }}</td>
                                            <td>{{ $u['departemen'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if (isset($assignedUsers) && $assignedUsers->count())
                        <h6 class="mt-2">Assigned Users - Middle DB (RAW)</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px">#</th>
                                        <th>SAP User</th>
                                        <th>Employee Name</th>
                                        <th>NIK</th>
                                        <th>Kompartemen</th>
                                        <th>Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($assignedUsers as $i => $u)
                                        <tr>
                                            <td class="text-end">{{ $i + 1 }}</td>
                                            <td>{{ $u['sap_user'] }}</td>
                                            <td class="{{ $u['employee_name'] === '-' ? 'text-muted' : '' }}">
                                                {{ $u['employee_name'] }}</td>
                                            <td>{{ $u['nik'] ?? '-' }}</td>
                                            <td>{{ $u['kompartemen'] }}</td>
                                            <td>{{ $u['departemen'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($summary)
                        <hr>
                        <div class="row g-3 mb-4">
                            <div class="col-auto">
                                <div class="small text-muted">Singles Local:</div>
                                <div>{{ $summary['local_single_count'] }}</div>
                            </div>
                            <div class="col-auto">
                                <div class="small text-muted">Singles RAW:</div>
                                <div>{{ $summary['raw_single_count'] }}</div>
                            </div>
                            <div class="col-auto">
                                <div class="small text-muted">Tcodes Local:</div>
                                <div>{{ $summary['local_tcode_count'] }}</div>
                            </div>
                            <div class="col-auto">
                                <div class="small text-muted">Tcodes RAW:</div>
                                <div>{{ $summary['raw_tcode_count'] }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="alert alert-warning py-2 small">
                        Row dengan Highlight Kuning adalah Row data yang memiliki <strong>perbedaan
                            antara data Lokal dan Middle DB.</strong>
                    </div>

                    <h6>1. Single Roles</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">#</th>
                                    <th>Local</th>
                                    <th>Middle DB (RAW)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($singleOrdered as $idx => $row)
                                    @php $isDiff = ($row['local'] === '-' xor $row['raw'] === '-'); @endphp
                                    <tr class="{{ $isDiff ? 'table-warning' : '' }}">
                                        <td class="text-end">{{ $idx + 1 }}</td>
                                        <td class="{{ $row['local'] === '-' ? 'text-muted' : '' }}">{{ $row['local'] }}
                                        </td>
                                        <td class="{{ $row['raw'] === '-' ? 'text-muted' : '' }}">{{ $row['raw'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No single roles found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6>2. Tcodes (Distinct)</h6>
                    <div class="table-responsive">
                        <table id="tcodeTable" class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:2.5%">#</th>
                                    <th>Local</th>
                                    <th>Middle DB (RAW)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tcodeOrdered as $idx => $row)
                                    @php $isDiff = ($row['local'] === '-' xor $row['raw'] === '-'); @endphp
                                    <tr class="{{ $isDiff ? 'table-warning' : '' }}">
                                        <td class="text-end">{{ $idx + 1 }}</td>
                                        <td class="{{ $row['local'] === '-' ? 'text-muted' : '' }}">{{ $row['local'] }}
                                        </td>
                                        <td class="{{ $row['raw'] === '-' ? 'text-muted' : '' }}">{{ $row['raw'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No tcodes found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && $.fn.DataTable) {
                $('#tcodeTable').DataTable({
                    pageLength: 50,
                    lengthMenu: [
                        [50, 75, 100],
                        [50, 75, 100]
                    ],
                    ordering: true,
                    searching: false,
                    info: true,
                    deferRender: true,
                    language: {
                        lengthMenu: 'Show _MENU_ tcodes per page'
                    }
                });
            }
        });
    </script>
@endsection
