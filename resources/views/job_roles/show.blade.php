<div>
    <h3>{{ $jobRole->nama_jabatan }}</h3>
    <p><strong>Company:</strong> {{ $jobRole->company->name ?? 'N/A' }}</p>
    <p><strong>Kompartemen:</strong> {{ $jobRole->kompartemen->name ?? 'N/A' }}</p>
    <p><strong>Departemen:</strong> {{ $jobRole->departemen->name ?? 'N/A' }}</p>
    <p><strong>Composite Role:</strong> {{ $jobRole->compositeRole->nama ?? 'Not Assigned' }}</p>
    <p><strong>Description:</strong> {{ $jobRole->deskripsi ?? 'None' }}</p>
</div>
