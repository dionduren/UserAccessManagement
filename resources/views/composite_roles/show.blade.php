<div>
    <h3>{{ $compositeRole->nama }}</h3>
    <p><strong>Company:</strong> {{ $compositeRole->company->name ?? 'N/A' }}</p>
    <p><strong>Kompartemen:</strong> {{ $compositeRole->jobRole->kompartemen->name ?? 'N/A' }}</p>
    <p><strong>Departemen:</strong> {{ $compositeRole->jobRole->departemen->name ?? 'N/A' }}</p>
    <p><strong>Job Role:</strong> {{ $compositeRole->jobRole->nama_jabatan ?? 'Not Assigned' }}</p>
    <p><strong>Description:</strong> {{ $compositeRole->deskripsi ?? 'None' }}</p>
</div>
