<div>
    <form id="createSingleRoleForm" class="ajax-modal-form" method="POST" action="{{ route('single-roles.store') }}">
        @csrf
        <div class="form-group">
            <label for="nama">Single Role Name</label>
            <input type="text" class="form-control" name="nama" id="nama" required>
        </div>
        <div class="form-group">
            <label for="deskripsi">Description</label>
            <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
