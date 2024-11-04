<div>
    <h3>{{ $singleRole->name }}</h3>
    <p><strong>Company:</strong> {{ $singleRole->company->name ?? 'N/A' }}</p>
    <p><strong>Composite Role:</strong> {{ $singleRole->compositeRole->nama ?? 'Not Assigned' }}</p>
    <p><strong>Description:</strong> {{ $singleRole->description ?? 'None' }}</p>

    @if ($singleRole->tcodes->isNotEmpty())
        <p><strong>Tcodes:</strong></p>
        <ul>
            @foreach ($singleRole->tcodes as $tcode)
                <li>{{ $tcode->code }} - {{ $tcode->description }}</li>
            @endforeach
        </ul>
    @else
        <p><strong>Tcodes:</strong> None assigned</p>
    @endif
</div>
