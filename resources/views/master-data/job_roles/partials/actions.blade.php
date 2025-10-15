@php
    // Prefer job_role_id; fallback to numeric id
    $param = $jobRole->job_role_id ?: $jobRole->id ?? null;
@endphp

@if ($param)
    <a href="{{ route('job-roles.show', ['job_role' => $param]) }}" class="btn btn-info btn-sm show-job-role"
        title="Detail">
        <i class="bi bi-eye"></i>
    </a>
    <a href="{{ route('job-roles.edit', ['job_role' => $param]) }}" class="btn btn-warning btn-sm" title="Edit">
        <i class="bi bi-pencil"></i>
    </a>
    <form action="{{ route('job-roles.destroy', ['job_role' => $param]) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
            <i class="bi bi-trash"></i>
        </button>
    </form>
    <a href="{{ route('job-roles.edit-flagged', ['job_role' => $param]) }}" class="btn btn-secondary btn-sm"
        title="Edit Flagged" target="_blank">
        <i class="bi bi-flag"></i>
    </a>
@else
    <button class="btn btn-secondary btn-sm" title="Unavailable" disabled><i class="bi bi-slash-circle"></i></button>
@endif
