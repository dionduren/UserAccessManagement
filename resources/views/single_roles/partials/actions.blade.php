{{-- <tr data-id="{{ $singleRole->id }}">
    <td>{{ $singleRole->company->name ?? 'N/A' }}</td>
    <td>{{ $singleRole->nama }}</td>
    <td>{{ $singleRole->deskripsi ?? 'None' }}</td>
    <td>
        <button class="btn btn-info btn-sm show-single-role" data-id="{{ $singleRole->id }}">
            <i class="bi bi-eye"></i>
        </button>
        <button class="btn btn-warning btn-sm edit-single-role" data-id="{{ $singleRole->id }}">
            <i class="bi bi-pencil"></i>
        </button>
        <form action="{{ route('single-roles.destroy', $singleRole) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </td>
</tr> --}}
<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-primary show-single-role" data-id="{{ $row->id }}"><i
            class="bi bi-eye"></i></button>
    <button type="button" class="btn btn-sm btn-warning edit-single-role mx-1" data-id="{{ $row->id }}"><i
            class="bi bi-pencil"></i></button>
    <form action="{{ route('single-roles.destroy', $row->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i
                class="bi bi-trash"></i></button>
    </form>
</div>
