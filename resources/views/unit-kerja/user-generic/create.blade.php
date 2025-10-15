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
        $(document).ready(function() {
            const $periode = $('#periode_id');
            const $company = $('#company_code');
            const $userCc = $('#user_cc');
            const $kompartemen = $('#kompartemen_id');
            const $departemen = $('#departemen_id');

            // Initialize Select2 for user search (disabled until periode selected)
            $userCc.select2({
                ajax: {
                    url: "{{ route('unit_kerja.user_generic.search_users') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            company: $company.val(),
                            periode_id: $periode.val(), // Include periode filter
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
                minimumInputLength: 1,
                disabled: true // Start disabled
            });

            // Enable/disable user search based on periode selection
            $periode.on('change', function() {
                const hasperiode = $(this).val();
                $userCc.prop('disabled', !hasperiode);
                if (!hasperiode) {
                    $userCc.val(null).trigger('change');
                }
            });

            // Refresh user search when company changes
            $company.on('change', function() {
                if ($periode.val()) {
                    $userCc.val(null).trigger('change'); // Clear selection
                }
                loadCompanyStructure();
            });

            function loadCompanyStructure() {
                const companyCode = $company.val();
                if (!companyCode) {
                    resetDropdowns();
                    return;
                }

                $.get("{{ route('unit_kerja.user_generic.company_structure') }}", {
                        company: companyCode
                    })
                    .done(function(data) {
                        populateKompartemen(data.kompartemen, data.departemen_by_kompartemen);
                        populateDepartemenWo(data.departemen_wo);
                    })
                    .fail(function() {
                        resetDropdowns();
                    });
            }

            function populateKompartemen(kompartemen, departemenByKomp) {
                $kompartemen.empty().append('<option value="">-- Select Kompartemen --</option>');
                if (kompartemen.length > 0) {
                    kompartemen.forEach(k => {
                        $kompartemen.append(`<option value="${k.id}">${k.text}</option>`);
                    });
                    $kompartemen.prop('disabled', false);
                } else {
                    $kompartemen.prop('disabled', true);
                }

                // Store departemen mapping for kompartemen change handler
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

                // Add departemen from selected kompartemen
                if (selectedKomp && window.departemenByKompartemen && window.departemenByKompartemen[
                        selectedKomp]) {
                    options = options.concat(window.departemenByKompartemen[selectedKomp]);
                }

                // Add departemen without kompartemen
                if (window.departemenWithoutKompartemen) {
                    options = options.concat(window.departemenWithoutKompartemen);
                }

                if (options.length > 0) {
                    options.forEach(d => {
                        $departemen.append(`<option value="${d.id}">${d.text}</option>`);
                    });
                    $departemen.prop('disabled', false);
                } else {
                    $departemen.prop('disabled', true);
                }
            }

            function resetDropdowns() {
                [$kompartemen, $departemen].forEach($el => {
                    $el.empty().append('<option value="">-- Select --</option>').prop('disabled', true);
                });
                window.departemenByKompartemen = {};
                window.departemenWithoutKompartemen = [];
            }

            // Handle kompartemen change
            $kompartemen.on('change', updateDepartemenOptions);

            // Initial state
            if ($periode.val()) {
                $userCc.prop('disabled', false);
            }
        });
    </script>
@endsection
