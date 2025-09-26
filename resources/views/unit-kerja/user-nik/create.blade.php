@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h5 mb-0">Create User NIK - Unit Kerja</h1>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="fw-bold mb-2">Silakan perbaiki:</div>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="form-unik" method="POST" action="{{ route('unit_kerja.user_nik.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="periode_id" class="form-label">Periode<span
                                            class="text-danger">*</span></label>
                                    <select id="periode_id" name="periode_id" class="form-select" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach ($periodes as $p)
                                            <option value="{{ $p->id }}" @selected(old('periode_id') == $p->id)>
                                                {{ $p->definisi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="company_id" class="form-label">Perusahaan</label>
                                    <select id="company_id" name="company_id" class="form-select">
                                        <option value="">-- Pilih Perusahaan --</option>
                                        @foreach ($companies as $c)
                                            <option value="{{ $c->company_code }}" @selected(old('company_id') == $c->company_code)>
                                                {{ $c->company_code }} - {{ $c->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="kompartemen_id" class="form-label">Kompartemen</label>
                                    <select id="kompartemen_id" name="kompartemen_id" class="form-select" disabled>
                                        @if (old('kompartemen_id'))
                                            <option value="{{ old('kompartemen_id') }}" selected>{{ old('kompartemen_id') }}
                                            </option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="departemen_id" class="form-label">Departemen</label>
                                    <select id="departemen_id" name="departemen_id" class="form-select" disabled>
                                        @if (old('departemen_id'))
                                            <option value="{{ old('departemen_id') }}" selected>{{ old('departemen_id') }}
                                            </option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="nama" class="form-label">Nama<span class="text-danger">*</span></label>
                                    <input type="text" id="nama" name="nama" class="form-control" required
                                        value="{{ old('nama') }}">
                                </div>

                                <div class="col-12">
                                    <label for="nik" class="form-label">NIK<span class="text-danger">*</span></label>
                                    <input type="text" id="nik" name="nik" class="form-control" required
                                        value="{{ old('nik') }}">
                                </div>

                                <div class="col-12">
                                    <label for="atasan" class="form-label">Atasan</label>
                                    <input type="text" id="atasan" name="atasan" class="form-control"
                                        value="{{ old('atasan') }}">
                                </div>

                                <div class="col-12">
                                    <label for="cost_center" class="form-label">Cost Center</label>
                                    <input type="text" id="cost_center" name="cost_center" class="form-control"
                                        value="{{ old('cost_center') }}">
                                </div>

                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                                <a href="{{ route('unit_kerja.user_nik.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
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

        function resetKompartemenAndDepartemen(disableKompartemen = true) {
            $('#kompartemen_id').empty().val(null).trigger('change').prop('disabled', disableKompartemen);
            $('#departemen_id').empty().val(null).trigger('change').prop('disabled', true);
        }

        function fillDepartemenOptions(options) {
            const $d = $('#departemen_id');
            $d.empty().append(new Option('', '', false, false));
            (options || []).forEach(d => $d.append(new Option(d.text, d.id, false, false)));
            $d.prop('disabled', !(options && options.length));
            $d.val(null).trigger('change');
        }

        function loadCompanyStructure(companyCode) {
            if (!companyCode) {
                resetKompartemenAndDepartemen(true);
                return;
            }

            fetch("{{ route('unit_kerja.user_generic.company_structure') }}?company=" + encodeURIComponent(companyCode), {
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

                    fillDepartemenOptions(DEP_WO);
                })
                .catch(() => resetKompartemenAndDepartemen(true));
        }

        $(function() {
            initSelect2();
            resetKompartemenAndDepartemen(true);

            $('#company_id').on('change', function() {
                const company = $(this).val();
                loadCompanyStructure(company);
            });

            $('#kompartemen_id').on('change', function() {
                const kompartemenId = $(this).val();
                if (!kompartemenId) {
                    fillDepartemenOptions(DEP_WO);
                    return;
                }
                fillDepartemenOptions(DEPT_BY_KOMP[kompartemenId] || []);
            });

            const oldCompany = @json(old('company_id'));
            const oldKompartemen = @json(old('kompartemen_id'));
            const oldDepartemen = @json(old('departemen_id'));

            if (oldCompany) {
                $('#company_id').val(oldCompany).trigger('change');
                setTimeout(() => {
                    if (oldKompartemen) {
                        $('#kompartemen_id').val(oldKompartemen).trigger('change');
                        setTimeout(() => {
                            if (oldDepartemen) $('#departemen_id').val(oldDepartemen).trigger(
                                'change');
                        }, 150);
                    } else if (oldDepartemen) {
                        setTimeout(() => $('#departemen_id').val(oldDepartemen).trigger('change'), 150);
                    }
                }, 350);
            }
        });
    </script>
@endsection
