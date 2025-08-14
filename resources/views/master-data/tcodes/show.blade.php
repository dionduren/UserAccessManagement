<div>
    <h3>{{ $tcode->code }}</h3>
    <p><strong>Description:</strong> {{ $tcode->deskripsi ?? 'None' }}</p>

    @if ($tcode->singleRoles->isNotEmpty())
        <p><strong>Assigned Single Roles:</strong></p>
        <ul>
            @foreach ($tcode->singleRoles as $singleRole)
                <li>{{ $singleRole->nama }} - {{ $singleRole->deskripsi }}</li>
            @endforeach
        </ul>
    @else
        <p><strong>Assigned Single Roles:</strong> None assigned</p>
    @endif
</div>
