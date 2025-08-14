<div>
    <form id="createSingleRoleForm" class="ajax-modal-form" method="POST" action="{{ route('single-roles.store') }}">
        @csrf
        <div class="form-group">
            <label for="company_id">Company</label>
            <select name="company_id" id="company_id" class="form-control select2" required>
                <option value="">Pilih perusahaan</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                @endforeach
            </select>
        </div>
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
