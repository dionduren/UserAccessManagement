<div class="modal fade" id="showSingleRoleModal" tabindex="-1" aria-labelledby="showSingleRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showSingleRoleModalLabel">Single Role Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-single-role-details">
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

            </div>
        </div>
    </div>
</div>
