<div>
    <h3>{{ $compositeRole->nama }}</h3>
    <p><strong>Company:</strong> {{ $compositeRole->company->nama ?? 'N/A' }}</p>
    <p><strong>Kompartemen:</strong> {{ $compositeRole->kompartemen->nama ?? 'N/A' }}</p>
    <p><strong>Departemen:</strong> {{ $compositeRole->departemen->nama ?? 'N/A' }}</p>
    <p><strong>Job Role:</strong> {{ $compositeRole->jobRole->nama ?? 'Not Assigned' }}</p>
    <p><strong>Description:</strong> {{ $compositeRole->deskripsi ?? 'No Description' }}</p>

    <h5>Associated Single Roles (Total: {{ $compositeRole->singleRoles()->count() }})</h5>
    <div class="overflow-auto" style="max-height: 400px;"> <!-- Scrollable container -->
        <ol>
            @forelse ($compositeRole->singleRoles as $singleRole)
                <li>{{ $singleRole->nama }}</li>
            @empty
                <li>No Single Roles Assigned</li>
            @endforelse
        </ol>
    </div>
</div>
