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
                                    <select id="kompartemen_id" name="kompartemen_id" class="form-select">
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
                                    <select id="departemen_id" name="departemen_id" class="form-select">
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
                                    <select id="user_cc" name="user_cc" class="form-select" required>
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
        $(document).ready(function() {
            const $periode = $('#periode_id');
            const $company = $('#company_code');
            const $userCc = $('#user_cc');
            const $kompartemen = $('#kompartemen_id');
            const $departemen = $('#departemen_id');

            // Initialize Select2 for user search
            $userCc.select2({
                ajax: {
                    url: "{{ route('unit_kerja.user_generic.search_users') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            company: $company.val(),
                            periode_id: $periode.val(),
                            mode: 'edit',
                            editing_user_id: @json($userGenericUnitKerja->user_cc)
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results || []
                        };
                    },
                    cache: true
                },
                placeholder: 'Search User...',
            });

            // Add current user to Select2 if not found in search
            const currentUser = @json($userGenericUnitKerja->user_cc);
            if (currentUser) {
                const currentText = currentUser + ' - ' + @json(data_get($userGenericUnitKerja, 'userGeneric.user_profile', 'Unknown'));
                const option = new Option(currentText, currentUser, true, true);
                $userCc.append(option).trigger('change');
            }

            function loadCompanyStructure(callback = null) {
                const companyCode = $company.val();
                if (!companyCode) {
                    resetDropdowns();
                    if (callback) callback();
                    return;
                }

                $.get("{{ route('unit_kerja.user_generic.company_structure') }}", {
                        company: companyCode
                    })
                    .done(function(data) {
                        populateKompartemen(data.kompartemen, data.departemen_by_kompartemen);
                        populateDepartemenWo(data.departemen_wo);
                        if (callback) callback();
                    })
                    .fail(function() {
                        resetDropdowns();
                        if (callback) callback();
                    });
            }

            function populateKompartemen(kompartemen, departemenByKomp) {
                $kompartemen.empty().append('<option value="">-- Select Kompartemen --</option>');
                if (kompartemen.length > 0) {
                    kompartemen.forEach(k => {
                        $kompartemen.append(`<option value="${k.id}">${k.text}</option>`);
                    });
                }

                window.departemenByKompartemen = departemenByKomp;
            }

            function populateDepartemenWo(departemenWo) {
                window.departemenWithoutKompartemen = departemenWo;
                updateDepartemenOptions();
            }

            function updateDepartemenOptions() {
                $departemen.empty().append('<option value="">-- Select Departemen --</option>');

                const selectedKomp = $kompartemen.val();
                let options = [];

                if (selectedKomp && window.departemenByKompartemen && window.departemenByKompartemen[
                        selectedKomp]) {
                    options = options.concat(window.departemenByKompartemen[selectedKomp]);
                }

                if (window.departemenWithoutKompartemen) {
                    options = options.concat(window.departemenWithoutKompartemen);
                }

                if (options.length > 0) {
                    options.forEach(d => {
                        $departemen.append(`<option value="${d.id}">${d.text}</option>`);
                    });
                }
            }

            function resetDropdowns() {
                [$kompartemen, $departemen].forEach($el => {
                    $el.empty().append('<option value="">-- Select --</option>');
                });
                window.departemenByKompartemen = {};
                window.departemenWithoutKompartemen = [];
            }

            // Event handlers
            $company.on('change', function() {
                loadCompanyStructure();
            });

            $kompartemen.on('change', updateDepartemenOptions);

            // Set initial company and load structure
            const initialCompany = @json($selectedCompany);
            if (initialCompany) {
                $company.val(initialCompany);
                loadCompanyStructure(() => {
                    const initialKomp = @json($userGenericUnitKerja->kompartemen_id);
                    const initialDept = @json($userGenericUnitKerja->departemen_id);

                    if (initialKomp) {
                        $kompartemen.val(initialKomp).trigger('change');
                    }
                    if (initialDept) {
                        setTimeout(() => $departemen.val(initialDept), 100);
                    }
                });
            }
        });
    </script>
@endsection
