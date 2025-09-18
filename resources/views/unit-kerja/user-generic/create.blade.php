@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h5 mb-0">Create User Generic - Unit Kerja</h1>
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

                        <form id="form-uguk" method="POST" action="{{ route('unit_kerja.user_generic.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="periode_id" class="form-label">Periode</label>
                                    <select id="periode_id" name="periode_id" class="form-select" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach ($periodes as $p)
                                            <option value="{{ $p->id }}"
                                                {{ old('periode_id') == $p->id ? 'selected' : '' }}>
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
                                                {{ old('company_code') == $c->company_code ? 'selected' : '' }}>
                                                {{ $c->company_code }} - {{ $c->nama }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="col-12">
                                    <label for="kompartemen_id" class="form-label">Kompartemen</label>
                                    <select id="kompartemen_id" name="kompartemen_id" class="form-select" disabled>
                                        @if (old('kompartemen_id'))
                                            <option value="{{ old('kompartemen_id') }}" selected>
                                                {{ old('kompartemen_id') }}</option>
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
                                    <label for="user_cc" class="form-label">User CC (User Code)</label>
                                    <select id="user_cc" name="user_cc" class="form-select" required disabled>
                                        @if (old('user_cc'))
                                            <option value="{{ old('user_cc') }}" selected>{{ old('user_cc') }}</option>
                                        @endif
                                    </select>
                                </div>

                                <!-- Removed: error_* fields, flagged, keterangan_flagged -->
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save
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
        let DEPT_BY_KOMP = {}; // { kompartemen_id: [{id,text}] }
        let DEP_WO = []; // [{id,text}]

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
                        return data; // {results: [...]}
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
                    // Save maps
                    DEPT_BY_KOMP = json.departemen_by_kompartemen || {};
                    DEP_WO = json.departemen_wo || [];

                    // Fill Kompartemen
                    const komps = json.kompartemen || [];
                    const $k = $('#kompartemen_id');
                    $k.empty().append(new Option('', '', false, false));
                    komps.forEach(k => $k.append(new Option(k.text, k.id, false, false)));
                    // Enable Kompartemen only if there are items for this company
                    $k.prop('disabled', komps.length === 0);

                    // Initial fill for Departemen:
                    // - If user chooses a kompartemen later, departemen will be replaced
                    // - If user doesn’t choose kompartemen, show "departemen tanpa kompartemen"
                    fillDepartemenOptions(DEP_WO);
                })
                .catch(() => {
                    resetKompartemenAndDepartemen(true);
                });
        }

        $(function() {
            initSelect2();

            // Initial states
            toggleUserCC();
            resetKompartemenAndDepartemen(true);

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
                    // No kompartemen chosen: show departemen_without_kompartemen
                    fillDepartemenOptions(DEP_WO);
                    return;
                }

                const arr = DEPT_BY_KOMP[kid] || [];
                fillDepartemenOptions(arr);
            });

            // Restore old selections (if validation failed)
            const oldCompany = @json(old('company_code'));
            const oldKomp = @json(old('kompartemen_id'));
            const oldDept = @json(old('departemen_id'));
            if (oldCompany) {
                $('#company_code').val(oldCompany).trigger('change');
                setTimeout(() => {
                    if (oldKomp) {
                        $('#kompartemen_id').val(oldKomp).trigger('change');
                        setTimeout(() => {
                            if (oldDept) $('#departemen_id').val(oldDept).trigger('change');
                        }, 150);
                    } else if (oldDept) {
                        // No kompartemen previously: departemen came from DEP_WO
                        setTimeout(() => {
                            $('#departemen_id').val(oldDept).trigger('change');
                        }, 150);
                    }
                }, 400);
            }
        });
    </script>
@endsection
