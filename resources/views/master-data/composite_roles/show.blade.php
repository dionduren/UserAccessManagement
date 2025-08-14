<div>
    <h3>{{ $compositeRole->nama }}</h3>
    <p><strong>Company:</strong> {{ $compositeRole->company->nama ?? 'N/A' }}</p>
    <p><strong>Description:</strong> {{ $compositeRole->deskripsi ?? 'No Description' }}</p>
    <hr>

    <p><strong>Job Role:</strong> {{ $compositeRole->jobRole->nama ?? 'Not Assigned' }}</p>

    <h5>Associated Single Roles</h5>
    <ul>
        @forelse ($compositeRole->singleRoles as $singleRole)
            <li>{{ $singleRole->nama }}</li>
        @empty
            <li>No Single Roles Assigned</li>
        @endforelse
    </ul>
</div>
