@php
    $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;
    // Adjust property name if different (company_id / company_code on tcode model)
    $canModify = $userCompanyCode === 'A000' || $tcode->company_id === $userCompanyCode;
@endphp
<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-primary show-tcode" data-id="{{ urlencode($tcode->id) }}">
        <i class="bi bi-eye"></i>
    </button>
    @if ($canModify)
        <button type="button" class="btn btn-sm btn-warning edit-tcode mx-1" data-id="{{ $tcode->id }}">
            <i class="bi bi-pencil"></i>
        </button>
        <form action="{{ route('tcodes.destroy', $tcode->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</div>
{{-- @if (!$canModify)
    <small class="text-muted d-block">Read only</small>
@endif --}}
