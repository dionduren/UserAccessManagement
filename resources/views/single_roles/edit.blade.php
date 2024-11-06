<div class="modal fade" id="editSingleRoleModal" tabindex="-1" aria-labelledby="editSingleRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editSingleRoleForm" method="POST" action="{{ route('single-roles.update', $singleRole->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editSingleRoleModalLabel">Edit Single Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form fields -->
                    <div class="form-group">
                        <input type="hidden" name="id" value="{{ $singleRole->id }}">

                        <label for="edit_company_id">Company</label>
                        <select name="company_id" id="edit_company_id" class="form-control select2">
                            <option value="">Select a company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ $company->id == $singleRole->company_id ? 'selected' : '' }}>{{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama">Single Role Name</label>
                        <input type="text" class="form-control" name="nama" id="edit_nama"
                            value="{{ $singleRole->nama }}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_deskripsi">Description</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi">{{ $singleRole->deskripsi }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
