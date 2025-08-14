<div>
    <h3>{{ $jobRole->nama }}</h3>
    <p><strong>Company:</strong> {{ $jobRole->company->nama ?? 'N/A' }}</p>
    <p><strong>Kompartemen:</strong> {{ $jobRole->kompartemen->nama ?? 'N/A' }}</p>
    <p><strong>Departemen:</strong> {{ $jobRole->departemen->nama ?? 'N/A' }}</p>
    <p><strong>Description:</strong> {{ $jobRole->deskripsi ?? 'None' }}</p>
    <hr>
    <p><strong>Composite Role:</strong> {{ $jobRole->compositeRole->nama ?? 'Not Assigned' }}</p>
</div>
