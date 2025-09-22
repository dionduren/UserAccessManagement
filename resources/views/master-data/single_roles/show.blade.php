<div>
    <h3>{{ $singleRole->nama }}</h3>
    <p><strong>Description:</strong> {{ $singleRole->deskripsi ?? 'None' }}</p>
    <p><strong>Composite Roles:</strong></p>
    @if ($singleRole->compositeRoles->isNotEmpty())
        <ul>
            {{-- @php
                $count = 0;
            @endphp
            @foreach ($singleRole->compositeRoles as $compositeRole)
                @if ($userCompanyCode == 'A000' && $compositeRole->company_code != 'A000')
                    <li>{{ $compositeRole->nama }}</li>
                @elseif ($userCompanyCode != 'A000' && $compositeRole->company_code == $userCompanyCode)
                    <li>{{ $compositeRole->nama }}</li>
                @else
                    @php
                        $count++;
                    @endphp
                @endif
            @endforeach
            @if ($count > 0)
                <p>Single role ini tidak diassign pada Composite Role terdaftar yang pada perusahaan anda</p>
            @endif --}}
            @foreach ($singleRole->compositeRoles->sortBy('nama') as $compositeRole)
                <li>{{ $compositeRole->nama }}</li>
            @endforeach
        </ul>
    @else
        <p>Tidak ada Composite Roles yang diassign pada Single Role ini</p>
    @endif

    <p><strong>Connected Tcodes:</strong></p>
    @if ($singleRole->tcodes->isNotEmpty())
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Tcode</th>
                    <th>Definisi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($singleRole->tcodes as $tcode)
                    <tr>
                        <td>{{ $tcode->code }}</td>
                        <td>{{ $tcode->deskripsi ?? 'No Description' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada Tcodes yang terhubung dengan Single Role ini</p>
    @endif
</div>
