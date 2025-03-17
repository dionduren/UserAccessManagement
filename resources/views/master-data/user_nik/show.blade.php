/************* ✨ Codeium Command 🌟 *************/
<!-- Modal -->
<div class="modal fade" id="userNIKModal" tabindex="-1" aria-labelledby="userNIKModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userNIKModalLabel">User NIK Details - Data Periode
                    {{ $userNIK->periode->definisi }}</h5>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-3">NIK</dt>
                    <dd class="col-sm-9">{{ $userNIK->user_code ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Nama</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->nama ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Perusahaan</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->company->name ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Direktorat</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->direktorat ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Kompartemen</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->kompartemen->name ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Departemen</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->departemen->name ?? 'N/A' }}</dd>


                    <dt class="col-sm-3">Cost Center</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->cost_center ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Jabatan</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->jabatan ?? 'N/A' }}</dd>


                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $userNIK->userDetail->email ?? 'N/A' }}</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="$('#userNIKModal').modal('toggle');">Close</button>
            </div>
        </div>
    </div>
</div>

/****** f1ce5d9e-5502-45b0-ac2d-c6c8cb0c2613 *******/
