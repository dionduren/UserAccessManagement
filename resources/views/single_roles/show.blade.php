<div>
    <h3>{{ $singleRole->nama }}</h3>
    <p><strong>Company:</strong> {{ $singleRole->company->name ?? 'N/A' }}</p>
    <p><strong>Description:</strong> {{ $singleRole->deskripsi ?? 'None' }}</p>
    <p><strong>Composite Roles:</strong></p>
    @if ($singleRole->compositeRoles->isNotEmpty())
        <ul>
            @foreach ($singleRole->compositeRoles as $compositeRole)
                <li>{{ $compositeRole->nama }}</li>
            @endforeach
        </ul>
    @else
        <p>No Composite Roles Assigned</p>
    @endif

    <p><strong>Connected Tcodes:</strong></p>
    @if ($singleRole->tcodes->isNotEmpty())
        <ul>
            @foreach ($singleRole->tcodes as $tcode)
                <li>{{ $tcode->code }} - {{ $tcode->deskripsi ?? 'No Description' }}</li>
            @endforeach
        </ul>
    @else
        <p>No Tcodes Connected</p>
    @endif
</div>
