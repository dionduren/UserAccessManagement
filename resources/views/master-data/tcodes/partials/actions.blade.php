<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-primary show-tcode" data-id="{{ $tcode->code }}">
        <i class="bi bi-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning edit-tcode mx-1 " data-id="{{ $tcode->code }}">
        <i class="bi bi-pencil"></i>
    </button>
    <form action="{{ route('tcodes.destroy', $tcode->code) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
