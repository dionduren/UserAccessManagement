<div class="btn-group" role="group" aria-label="Actions">
    <a href="{{ $editUrl }}" class="btn btn-sm btn-primary">
        <i class="fas fa-edit"> Edit</i>
    </a>
    <form action="{{ $deleteUrl }}" method="POST" style="display: inline-block;"
        onsubmit="return confirm('Are you sure?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="fas fa-trash"> Delete</i>
        </button>
    </form>
</div>
