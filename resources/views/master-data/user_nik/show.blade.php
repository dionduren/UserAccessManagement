<!-- Modal -->
<div class="modal fade" id="userNIKModal" tabindex="-1" aria-labelledby="userNIKModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userNIKModalLabel">
                    User NIK Details - Data Periode {{ $userNIK->periode->definisi ?? 'N/A' }}
                </h5>
            </div>
            <div class="modal-body">

                @if (!$userNIK->unitKerja)
                    <div class="alert alert-warning small">
                        Detail Unit Kerja tidak ditemukan untuk NIK <strong>{{ $userNIK->user_code }}</strong>.
                        Pastikan tabel <code>user_nik_unit_kerja</code> sudah terisi (kolom nik =
                        {{ $userNIK->user_code }}).
                    </div>
                @endif

                <dl class="row">
                    <dt class="col-sm-3">NIK</dt>
                    <dd class="col-sm-9">{{ $userNIK->user_code ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Nama</dt>
                    <dd class="col-sm-9">{{ $userNIK->unitKerja->nama ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Perusahaan</dt>
                    <dd class="col-sm-9">{{ $userNIK->unitKerja->company->nama ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Direktorat</dt>
                    <dd class="col-sm-9">{{ $userNIK->unitKerja->direktorat->direktorat ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Kompartemen</dt>
                    <dd class="col-sm-9">{{ $userNIK->unitKerja->kompartemen->nama ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Departemen</dt>
                    <dd class="col-sm-9">{{ $userNIK->unitKerja->departemen->nama ?? 'N/A' }}</dd>

                    {{-- <dt class="col-sm-3">UserNIKUnitKerja</dt>
                    <dd class="col-sm-9">
                        {{ $userNIK }}
                    </dd> --}}



                    {{-- <dt class="col-sm-3">Cost Center</dt>
                    <dd class="col-sm-9">{{ $userNIK->UserNIKUnitKerja->cost_center ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Jabatan</dt>
                    <dd class="col-sm-9">{{ $userNIK->UserNIKUnitKerja->jabatan ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $userNIK->UserNIKUnitKerja->email ?? 'N/A' }}</dd> --}}
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="$('#userNIKModal').modal('toggle');">Close</button>
            </div>
        </div>
    </div>
</div>
