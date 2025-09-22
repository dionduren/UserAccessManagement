<div>
    <form id="editSingleRoleForm" class="ajax-modal-form" method="POST"
        action="{{ route('single-roles.update', ['single_role' => $singleRole->id]) }}">
        @csrf
        @method('PUT') <!-- Specify method spoofing for update -->

        <div class="form-group">
            <label for="nama">Single Role Name</label>
            <input type="text" class="form-control" name="nama" id="nama" value="{{ $singleRole->nama }}"
                required>
        </div>
        <div class="form-group">
            <label for="deskripsi">Description</label>
            <textarea class="form-control" name="deskripsi" id="deskripsi">{{ $singleRole->deskripsi }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
