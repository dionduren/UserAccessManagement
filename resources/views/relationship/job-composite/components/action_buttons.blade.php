{{-- @if ($role && $role->id)
    <a href="#" class="btn btn-info btn-sm show-composite-role" data-id="{{ $role->id ?? '' }}" data-toggle="modal"
        data-target="#CompositeRoleModal">
        <i class="bi bi-eye"></i>
    </a>

    <a href="{{ route('job-composite.edit', ['composite_role' => $role->id ?? '']) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil"></i>
    </a>

    <form action="{{ route('job-composite.destroy', ['composite_role' => $role->id ?? '']) }}" method="POST"
        style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@else
    <span class="text-danger">Invalid Role Data</span>
@endif --}}
@if ($role && $role->id)
    <div class="btn-group" role="group" aria-label="Actions">

        <!-- View Button -->
        <a href="#" class="btn btn-info btn-sm show-composite-role me-2" data-id="{{ $role->id }}"
            data-toggle="modal" data-target="#CompositeRoleModal" title="View Details">
            <i class="bi bi-eye"></i>
        </a>

        <!-- Edit Button -->
        <a href="{{ route('job-composite.edit', ['job_composite' => $role->id]) }}" class="btn btn-warning btn-sm me-2"
            title="Edit Relationship">
            <i class="bi bi-pencil"></i>
        </a>

        <!-- Delete Button -->
        <form action="{{ route('job-composite.destroy', ['job_composite' => $role->id]) }}" method="POST"
            style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this relationship?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" title="Delete Relationship">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </div>
@else
    <span class="text-danger">Invalid Role Data</span>
@endif
