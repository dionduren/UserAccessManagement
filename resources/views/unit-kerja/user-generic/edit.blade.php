@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h5 mb-0">Edit User Generic - Unit Kerja</h1>
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

                        <form id="form-uguk-edit" method="POST"
                            action="{{ route('unit_kerja.user_generic.update', $userGenericUnitKerja->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="periode_id" class="form-label">Periode</label>
                                    <select id="periode_id" name="periode_id" class="form-select" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach ($periodes as $p)
                                            <option value="{{ $p->id }}"
                                                {{ old('periode_id', $userGenericUnitKerja->periode_id) == $p->id ? 'selected' : '' }}>
                                                {{ $p->definisi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="company_code" class="form-label">Perusahaan</label>
                                    <select id="company_code" class="form-select">
                                        <option value="">-- Pilih Perusahaan --</option>
                                        @foreach ($companies as $c)
                                            <option value="{{ $c->company_code }}"
                                                {{ old('company_code', $selectedCompany) == $c->company_code ? 'selected' : '' }}>
                                                {{ $c->company_code }} - {{ $c->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block">Digunakan untuk filter User CC dan cascading
                                        Kompartemen/Departemen (tidak disimpan).</small>
                                </div>

                                <div class="col-12">
                                    <label for="kompartemen_id" class="form-label">Kompartemen</label>
                                    <select id="kompartemen_id" name="kompartemen_id" class="form-select" disabled>
                                        @php
                                            $oldKomp = old('kompartemen_id', $userGenericUnitKerja->kompartemen_id);
                                        @endphp
                                        @if ($oldKomp)
                                            <option value="{{ $oldKomp }}" selected>{{ $oldKomp }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="departemen_id" class="form-label">Departemen</label>
                                    <select id="departemen_id" name="departemen_id" class="form-select" disabled>
                                        @php
                                            $oldDept = old('departemen_id', $userGenericUnitKerja->departemen_id);
                                        @endphp
                                        @if ($oldDept)
                                            <option value="{{ $oldDept }}" selected>{{ $oldDept }}</option>
                                        @endif
                                    </select>
                                    <small class="text-muted">
                                        Jika Kompartemen dipilih, daftar departemen akan mengikuti Kompartemen.
                                        Jika Kompartemen tidak dipilih, daftar departemen menampilkan "Departemen tanpa
                                        Kompartemen".
                                    </small>
                                </div>

                                <div class="col-12">
                                    <label for="user_cc" class="form-label">User CC (User Code)</label>
                                    <select id="user_cc" name="user_cc" class="form-select" required disabled>
                                        @php
                                            $currentUserCode = old('user_cc', $userGenericUnitKerja->user_cc);
                                            $currentUserText = trim(
                                                ($currentUserCode ?? '') .
                                                    (isset($userGenericUnitKerja->userGeneric->user_profile)
                                                        ? ' - ' . $userGenericUnitKerja->userGeneric->user_profile
                                                        : ''),
                                            );
                                        @endphp
                                        @if ($currentUserCode)
                                            <option value="{{ $currentUserCode }}" selected>{{ $currentUserText }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update
                                </button>
                                <a href="{{ route('unit_kerja.user_generic.index') }}" class="btn btn-secondary">
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
            $('#periode_id').select2({
                width: '100%'
            });
            $('#company_code').select2({
                width: '100%'
            });
            $('#kompartemen_id').select2({
                width: '100%',
                placeholder: '-- Pilih Kompartemen --',
                allowClear: true
            });
            $('#departemen_id').select2({
                width: '100%',
                placeholder: '-- Pilih Departemen --',
                allowClear: true
            });
            $('#user_cc').select2({
                width: '100%',
                placeholder: 'Cari User Code / Nama...',
                allowClear: true,
                ajax: {
                    delay: 250,
                    url: "{{ route('unit_kerja.user_generic.search_users') }}",
                    dataType: 'json',
                    data: function(params) {
                        return {
                            q: params.term || '',
                            company: $('#company_code').val() || ''
                        };
                    },
                    processResults: function(data) {
                        return data;
                    }
                }
            });
        }

        function resetKompartemenAndDepartemen(disableKompartemen = true) {
            $('#kompartemen_id').empty().val(null).trigger('change').prop('disabled', disableKompartemen);
            $('#departemen_id').empty().val(null).trigger('change').prop('disabled', true);
        }

        function toggleUserCC() {
            const hasCompany = !!$('#company_code').val();
            $('#user_cc').prop('disabled', !hasCompany);
            if (!hasCompany) $('#user_cc').val(null).trigger('change');
        }

        function fillDepartemenOptions(options) {
            const $d = $('#departemen_id');
            $d.empty().append(new Option('', '', false, false));
            (options || []).forEach(d => $d.append(new Option(d.text, d.id, false, false)));
            $d.prop('disabled', !(options && options.length));
            $d.val(null).trigger('change');
        }

        // Returns a Promise to allow chaining (needed for preselecting existing values)
        function loadCompanyStructure(companyCode) {
            if (!companyCode) {
                resetKompartemenAndDepartemen(true);
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

                    // Fill Kompartemen
                    const komps = json.kompartemen || [];
                    const $k = $('#kompartemen_id');
                    $k.empty().append(new Option('', '', false, false));
                    komps.forEach(k => $k.append(new Option(k.text, k.id, false, false)));
                    $k.prop('disabled', komps.length === 0);

                    // Default fill with departemen tanpa kompartemen; will be replaced if a kompartemen is selected
                    fillDepartemenOptions(DEP_WO);
                    return json;
                })
                .catch(() => {
                    resetKompartemenAndDepartemen(true);
                });
        }

        $(function() {
            initSelect2();

            const initialCompany = @json(old('company_code', $selectedCompany));
            const initialKomp = @json(old('kompartemen_id', $userGenericUnitKerja->kompartemen_id));
            const initialDept = @json(old('departemen_id', $userGenericUnitKerja->departemen_id));

            // Initial enable/disable
            resetKompartemenAndDepartemen(true);
            if (initialCompany) $('#company_code').val(initialCompany).trigger('change');
            toggleUserCC();

            // Load structure for initial company and preselect existing values
            if (initialCompany) {
                loadCompanyStructure(initialCompany).then(() => {
                    if (initialKomp) {
                        // Set Kompartemen and then fill Departemen under that Kompartemen
                        $('#kompartemen_id').val(initialKomp).trigger('change');

                        const arr = DEPT_BY_KOMP[initialKomp] || [];
                        fillDepartemenOptions(arr);
                        if (initialDept) {
                            $('#departemen_id').val(initialDept).trigger('change');
                        }
                    } else {
                        // No Kompartemen saved: use Departemen WO
                        fillDepartemenOptions(DEP_WO);
                        if (initialDept) {
                            $('#departemen_id').val(initialDept).trigger('change');
                        }
                    }
                    toggleUserCC();
                });
            }

            // Company change
            $('#company_code').on('change', function() {
                const code = $(this).val();
                toggleUserCC();
                loadCompanyStructure(code);
            });

            // Kompartemen change -> switch departemen list
            $('#kompartemen_id').on('change', function() {
                const kid = $(this).val();
                if (!kid) {
                    fillDepartemenOptions(DEP_WO);
                    return;
                }
                const arr = DEPT_BY_KOMP[kid] || [];
                fillDepartemenOptions(arr);
            });
        });
    </script>
@endsection
