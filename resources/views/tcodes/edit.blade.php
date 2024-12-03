<div>
    <form id="editTcodeForm" method="POST" class="ajax-modal-form" action="{{ route('tcodes.update', $tcode->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label for="code">Tcode</label>
                <input type="text" class="form-control" name="code" id="code" value="{{ $tcode->code }}"
                    required>
            </div>
            <div class="form-group">
                <label for="sap_module">SAP Module</label>
                <input type="text" class="form-control" name="sap_module" id="sap_module"
                    value="{{ $tcode->sap_module }}">
            </div>
            <div class="form-group">
                <label for="deskripsi">Description</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi">{{ $tcode->deskripsi }}</textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
