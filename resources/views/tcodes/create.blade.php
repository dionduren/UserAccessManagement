<div>
    <form id="createTcodeForm" class="ajax-modal-form" method="POST" action="{{ route('tcodes.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label for="code">Tcode</label>
                <input type="text" class="form-control" name="code" id="code" required>
            </div>
            <div class="form-group">
                <label for="sap_module">SAP Module</label>
                <input type="text" class="form-control" name="sap_module" id="sap_module">
            </div>
            <div class="form-group">
                <label for="deskripsi">Description</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </form>
</div>
