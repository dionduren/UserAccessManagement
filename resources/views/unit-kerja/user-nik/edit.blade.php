@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h5 mb-0">Edit User NIK - Unit Kerja</h1>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="fw-bold mb-2">Please fix the following:</div>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="form-unuk-edit" method="POST"
                            action="{{ route('unit_kerja.user_nik.update', $userNIKUnitKerja->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="periode_id" class="form-label">Periode<span
                                            class="text-danger">*</span></label>
                                    <select id="periode_id" name="periode_id" class="form-select" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach ($periodes as $p)
                                            <option value="{{ $p->id }}" @selected(old('periode_id', $userNIKUnitKerja->periode_id) == $p->id)>
                                                {{ $p->definisi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="company_id" class="form-label">Perusahaan</label>
                                    <select id="company_id" name="company_id" class="form-select">
                                        <option value="">-- Pilih Perusahaan --</option>
                                        @foreach ($companies as $c)
                                            <option value="{{ $c->company_code }}" @selected(old('company_id', $selectedCompany) == $c->company_code)>
                                                {{ $c->company_code }} - {{ $c->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="direktorat_id" class="form-label">Direktorat</label>
                                    <input type="text" id="direktorat_id" name="direktorat_id" class="form-control"
                                        value="{{ old('direktorat_id', $userNIKUnitKerja->direktorat_id) }}">
                                </div>

                                <div class="col-12">
                                    <label for="kompartemen_id" class="form-label">Kompartemen</label>
                                    <select id="kompartemen_id" name="kompartemen_id" class="form-select" disabled>
                                        @if (old('kompartemen_id', $userNIKUnitKerja->kompartemen_id))
                                            <option value="{{ old('kompartemen_id', $userNIKUnitKerja->kompartemen_id) }}"
                                                selected>
                                                {{ old('kompartemen_id', $userNIKUnitKerja->kompartemen_id) }}
                                            </option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="departemen_id" class="form-label">Departemen</label>
                                    <select id="departemen_id" name="departemen_id" class="form-select" disabled>
                                        @if (old('departemen_id', $userNIKUnitKerja->departemen_id))
                                            <option value="{{ old('departemen_id', $userNIKUnitKerja->departemen_id) }}"
                                                selected>
                                                {{ old('departemen_id', $userNIKUnitKerja->departemen_id) }}
                                            </option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="nama" class="form-label">Nama<span class="text-danger">*</span></label>
                                    <input type="text" id="nama" name="nama" class="form-control" required
                                        value="{{ old('nama', $userNIKUnitKerja->nama) }}">
                                </div>

                                <div class="col-12">
                                    <label for="nik" class="form-label">NIK<span class="text-danger">*</span></label>
                                    <input type="text" id="nik" name="nik" class="form-control" required
                                        value="{{ old('nik', $userNIKUnitKerja->nik) }}">
                                </div>

                                <div class="col-12">
                                    <label for="atasan" class="form-label">Atasan</label>
                                    <input type="text" id="atasan" name="atasan" class="form-control"
                                        value="{{ old('atasan', $userNIKUnitKerja->atasan) }}">
                                </div>

                                <div class="col-12">
                                    <label for="cost_center" class="form-label">Cost Center</label>
                                    <input type="text" id="cost_center" name="cost_center" class="form-control"
                                        value="{{ old('cost_center', $userNIKUnitKerja->cost_center) }}">
                                </div>

                                <br>
                                <hr class="mx-auto mt-4 mb-0 pt-3" style="width: 90%;">
                                <small class="text-muted mb-3">Flagged & Error User Data</small>

                                <div class="col-12">
                                    <label for="error_kompartemen_id" class="form-label">Error Kompartemen ID</label>
                                    <input type="text" id="error_kompartemen_id" name="error_kompartemen_id"
                                        class="form-control"
                                        value="{{ old('error_kompartemen_id', $userNIKUnitKerja->error_kompartemen_id) }}">
                                </div>

                                <div class="col-12">
                                    <label for="error_kompartemen_name" class="form-label">Error Kompartemen Name</label>
                                    <input type="text" id="error_kompartemen_name" name="error_kompartemen_name"
                                        class="form-control"
                                        value="{{ old('error_kompartemen_name', $userNIKUnitKerja->error_kompartemen_name) }}">
                                </div>

                                <div class="col-12">
                                    <label for="error_departemen_id" class="form-label">Error Departemen ID</label>
                                    <input type="text" id="error_departemen_id" name="error_departemen_id"
                                        class="form-control"
                                        value="{{ old('error_departemen_id', $userNIKUnitKerja->error_departemen_id) }}">
                                </div>

                                <div class="col-12">
                                    <label for="error_departemen_name" class="form-label">Error Departemen Name</label>
                                    <input type="text" id="error_departemen_name" name="error_departemen_name"
                                        class="form-control"
                                        value="{{ old('error_departemen_name', $userNIKUnitKerja->error_departemen_name) }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Flagged?</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="flagged" name="flagged"
                                            value="1" @checked(old('flagged', $userNIKUnitKerja->flagged))>
                                        <label class="form-check-label" for="flagged">Tandai sebagai flagged</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea id="keterangan" name="keterangan" rows="3" class="form-control">{{ old('keterangan', $userNIKUnitKerja->keterangan) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update
                                </button>
                                <a href="{{ route('unit_kerja.user_nik.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let DEPT_BY_KOMP = {};
        let DEP_WO = [];

        function initSelect2() {
            $('#periode_id, #company_id, #kompartemen_id, #departemen_id').select2({
                width: '100%'
            });
        }

        function resetKompartemenDepartemen(disableKompartemen = true) {
            $('#kompartemen_id').empty().val(null).trigger('change').prop('disabled', disableKompartemen);
            $('#departemen_id').empty().val(null).trigger('change').prop('disabled', true);
        }

        function fillDepartemen(options) {
            const $d = $('#departemen_id');
            $d.empty().append(new Option('', '', false, false));
            (options || []).forEach(d => $d.append(new Option(d.text, d.id, false, false)));
            $d.prop('disabled', !(options && options.length));
            $d.val(null).trigger('change');
        }

        function loadCompanyStructure(companyCode) {
            if (!companyCode) {
                resetKompartemenDepartemen(true);
                return Promise.resolve();
            }

            return fetch("{{ route('unit_kerja.user_generic.company_structure') }}?company=" + encodeURIComponent(
                    companyCode), {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(json => {
                    DEPT_BY_KOMP = json.departemen_by_kompartemen || {};
                    DEP_WO = json.departemen_wo || [];

                    const komps = json.kompartemen || [];
                    const $k = $('#kompartemen_id');
                    $k.empty().append(new Option('', '', false, false));
                    komps.forEach(k => $k.append(new Option(k.text, k.id, false, false)));
                    $k.prop('disabled', komps.length === 0);

                    fillDepartemen(DEP_WO);
                    return json;
                })
                .catch(() => resetKompartemenDepartemen(true));
        }

        $(function() {
            initSelect2();
            const initialCompany = @json(old('company_id', $selectedCompany));
            const initialKompartemen = @json(old('kompartemen_id', $userNIKUnitKerja->kompartemen_id));
            const initialDepartemen = @json(old('departemen_id', $userNIKUnitKerja->departemen_id));

            resetKompartemenDepartemen(true);

            if (initialCompany) {
                $('#company_id').val(initialCompany).trigger('change');
                loadCompanyStructure(initialCompany).then(() => {
                    if (initialKompartemen) {
                        $('#kompartemen_id').val(initialKompartemen).trigger('change');
                        fillDepartemen(DEPT_BY_KOMP[initialKompartemen] || []);
                        if (initialDepartemen) {
                            $('#departemen_id').val(initialDepartemen).trigger('change');
                        }
                    } else {
                        fillDepartemen(DEP_WO);
                        if (initialDepartemen) {
                            $('#departemen_id').val(initialDepartemen).trigger('change');
                        }
                    }
                });
            }

            $('#company_id').on('change', function() {
                const company = $(this).val();
                loadCompanyStructure(company);
            });

            $('#kompartemen_id').on('change', function() {
                const kompartemenId = $(this).val();
                if (!kompartemenId) {
                    fillDepartemen(DEP_WO);
                    return;
                }
                fillDepartemen(DEPT_BY_KOMP[kompartemenId] || []);
            });
        });
    </script>
@endsection
