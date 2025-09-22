@php
    $userCompanyCode = auth()->user()->loginDetail->company_code ?? null;
    $canModify = $userCompanyCode === 'A000' || $role->company_id === $userCompanyCode;
@endphp

<div class="btn-group btn-group-s m" role="group">
    <button type="button" class="btn btn-info show-single-role" data-id="{{ $role->id }}">
        <i class="bi bi-eye"></i>
    </button>

    @if ($canModify)
        <button type="button" class="btn btn-warning edit-single-role" data-id="{{ $role->id }}">
            <i class="bi bi-pencil"></i>
        </button>
        <button type="button" class="btn btn-danger delete-single-role"
            data-url="{{ route('single-roles.destroy', $role->id) }}">
            <i class="bi bi-trash"></i>
        </button>
    @endif
</div>
@if (!$canModify)
    {{-- <small class="text-muted d-block">Tidak dapat diedit (perusahaan berbeda)</small> --}}
@endif
