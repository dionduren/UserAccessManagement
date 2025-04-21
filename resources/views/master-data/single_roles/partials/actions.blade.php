<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-primary show-single-role" data-id="{{ $role->id }}">
        <i class="bi bi-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning edit-single-role mx-1" data-id="{{ $role->id }}">
        <i class="bi bi-pencil"></i>
    </button>
    {{-- <form action="{{ route('single-roles.destroy', $role->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i
                class="bi bi-trash"></i></button>
    </form> --}}
    <button type="button" class="btn btn-sm btn-danger delete-single-role" data-id="{{ $role->id }}"
        data-url="{{ route('single-roles.destroy', $role->id) }}">
        <i class="bi bi-trash"></i>
    </button>
</div>
