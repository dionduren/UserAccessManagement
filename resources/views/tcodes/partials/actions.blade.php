<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-primary show-tcode" data-id="{{ $tcode->id }}">
        <i class="bi bi-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning mx-1 edit-tcode" data-id="{{ $tcode->id }}">
        <i class="bi bi-pencil"></i>
    </button>
    <form action="{{ route('tcodes.destroy', $tcode->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
