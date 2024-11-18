<a href="#" data-id="{{ $jobRole->id }}" class="btn btn-info btn-sm show-job-role" data-toggle="modal"
    data-target="#showJobRoleModal">
    <i class="bi bi-eye"></i>
</a>
<a href="{{ route('job-roles.edit', $jobRole) }}" class="btn btn-warning btn-sm">
    <i class="bi bi-pencil"></i>
</a>
<form action="{{ route('job-roles.destroy', $jobRole) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
        <i class="bi bi-trash"></i>
    </button>
</form>
