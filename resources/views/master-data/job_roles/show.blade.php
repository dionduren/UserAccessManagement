<div>
    <h3>{{ $jobRole->nama }}</h3>
    <p><strong>Company:</strong> {{ $jobRole->company->nama ?? 'N/A' }}</p>
    <p><strong>Kompartemen:</strong> {{ $jobRole->kompartemen->nama ?? 'N/A' }}</p>
    <p><strong>Departemen:</strong> {{ $jobRole->departemen->nama ?? 'N/A' }}</p>
    <p><strong>Description:</strong> {{ $jobRole->deskripsi ?? 'None' }}</p>
    <hr>
    @php
        // Try to resolve a collection of connected NIK job role records/users
        $nikConnections = collect();

        // Prefer a relationship named nikJobRoles (adjust if your actual relationship name differs)
        if (isset($jobRole->NIKJobRole)) {
            $nikConnections = collect($jobRole->NIKJobRole);
        } elseif (method_exists($jobRole, 'NIKJobRole')) {
            $nikConnections = $jobRole->NIKJobRole;
            ////////////////
        }
    @endphp

    @if ($nikConnections->count())
        <p><strong>Daftar NIK Job Role yang terhubung:</strong></p>
        <ol class="mb-2">
            @foreach ($nikConnections as $conn)
                <li>
                    {{ $conn->nik ?? ($conn->user->nik ?? '-') }}
                    @if (isset($conn->nama) || isset($conn->user))
                        - {{ $conn->nama ?? ($conn->user->nama ?? ($conn->user->name ?? '')) }}
                    @endif
                </li>
            @endforeach
        </ol>
    @else
        <p><strong>Daftar NIK Job Role yang terhubung:</strong> None</p>
    @endif
    <hr>
    <p><strong>Composite Role:</strong> {{ $jobRole->compositeRole->nama ?? 'Not Assigned' }}</p>
    <p><strong>Middle DB - Composite Roles (Unique):</strong>
        @php
            $middleCompositeNames = $jobRole->connectedMiddleCompositeRoles(true);
        @endphp
        {{ $middleCompositeNames->isNotEmpty() ? $middleCompositeNames->join(', ') : 'None' }}
    </p>

    @php
        // Detailed debug (row counts, sources)
        $middleCompositeDebug = $jobRole->connectedMiddleCompositeRoles(false, true);
    @endphp
    @if ($middleCompositeDebug->isNotEmpty())
        <hr>
        <h5>Middle DB Composite Role Links (Detail)</h5>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Composite Role</th>
                    <th>Description</th>
                    <th>Sources</th>
                    {{-- <th>Row Count</th> --}}
                    <th>NIK Users (Matched)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($middleCompositeDebug as $row)
                    <tr>
                        <td>{{ $row->composite_role }}</td>
                        <td>{{ $row->composite_role_desc }}</td>
                        <td>{{ $row->source }}</td>
                        {{-- <td>{{ $row->raw_row_count }}</td> --}}
                        <td>
                            @if ($row->nik_users && count($row->nik_users))
                                {{ collect($row->nik_users)->join(', ') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($jobRole->compositeRole)
        @php
            $aggCounts = $jobRole->compositeRole->aggregatedConnectivityCounts(); // counts only
        @endphp
        <hr>
        <h5>Connectivity Breakdown (Composite: {{ $jobRole->compositeRole->nama }})</h5>
        <table class="table table-sm table-bordered w-auto">
            <thead class="table-light">
                <tr>
                    <th>Metric</th>
                    <th>Middle DB</th>
                    <th>LOCAL</th>
                    <th>Overlap</th>
                    <th>Middle DB Only</th>
                    <th>Local Only</th>
                    <th>Total Distinct</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aggCounts as $row)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $row['metric'])) }}</td>
                        <td class="text-end">{{ $row['raw_count'] }}</td>
                        <td class="text-end">{{ $row['local_count'] }}</td>
                        <td class="text-end">{{ $row['overlap_count'] }}</td>
                        <td class="text-end">{{ $row['raw_only_count'] }}</td>
                        <td class="text-end">{{ $row['local_only_count'] }}</td>
                        <td class="text-end fw-semibold">{{ $row['total_distinct'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Optional detailed lists (uncomment if needed) --}}
        {{--
        @php
            $aggWithLists = $jobRole->compositeRole->aggregatedConnectivityCounts(true);
        @endphp
        <pre class="small bg-light p-2 border rounded">{{ json_encode($aggWithLists, JSON_PRETTY_PRINT) }}</pre>
        --}}
    @endif
</div>
