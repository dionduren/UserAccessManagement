<a href="#" data-id="{{ $role->id }}" class="btn btn-info btn-sm show-composite-role" data-toggle="modal"
    data-target="#showCompositeRoleModal">
    <i class="bi bi-eye"></i>
</a>
<a href="{{ route('composite-roles.edit', $role) }}" class="btn btn-warning btn-sm">
    <i class="bi bi-pencil"></i>
</a>
<form action="{{ route('composite-roles.destroy', $role) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
        <i class="bi bi-trash"></i>
    </button>
</form>
