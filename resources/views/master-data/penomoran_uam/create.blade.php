@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <h1>Create Penomoran UAM</h1>

            <form action="{{ route('penomoran-uam.store') }}" method="POST">
                @csrf
                <form
                    action="{{ isset($penomoranUAM) ? route('penomoran-uam.update', $penomoranUAM->id) : route('penomoran-uam.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($penomoranUAM))
                        @method('PUT')
                    @endif

                    <div class="form-group mb-3">
                        <label for="company_id">Company</label>
                        <select id="company_id" name="company_id" class="form-control" required>
                            <option value="">-- Select Company --</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company['company_id'] }}"
                                    {{ old('company_id', $selectedCompany ?? '') == $company['company_id'] ? 'selected' : '' }}>
                                    {{ $company['company_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="kompartemen_id">Kompartemen</label>
                        <select id="kompartemen_id" name="kompartemen_id" class="form-control" required>
                            <option value="">-- Select Kompartemen --</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="departemen_id">Departemen</label>
                        <select id="departemen_id" name="departemen_id" class="form-control">
                            <option value="">-- Select Departemen --</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="number">Number</label>
                        <input type="text" class="form-control" id="number" name="number"
                            value="{{ old('number', $penomoranUAM->number ?? '') }}" required>
                        <span id="number-error" class="text-danger"></span>
                    </div>

                    <div class="row">
                        <div class="col-auto"><button type="submit"
                                class="btn btn-primary mb-3">{{ isset($penomoranUAM) ? 'Update' : 'Create' }}</button>
                        </div>
                        <div class="col-auto"><a href="{{ route('penomoran-uam.index') }}" class="btn btn-secondary">Kembali
                                ke
                                Index</a>
                        </div>
                    </div>


                </form>



            </form>
        </div>
    @endsection

    @section('scripts')
        <script>
            let masterData = @json($masterData);

            $(document).ready(function() {
                // Populate Kompartemen when Company changes
                $('#company_id').on('change', function() {
                    let companyId = $(this).val();
                    let kompartemenSelect = $('#kompartemen_id');
                    kompartemenSelect.html('<option value="">-- Pilih Kompartemen --</option>');
                    let departemenSelect = $('#departemen_id');
                    departemenSelect.html('<option value="">-- Pilih Departemen --</option>');

                    let company = masterData.find(c => c.company_id === companyId);
                    if (company && company.kompartemen) {
                        // Sort kompartemen by nama ascending
                        let sortedKompartemen = [...company.kompartemen].sort((a, b) => a.nama.localeCompare(b
                            .nama));
                        sortedKompartemen.forEach(function(k) {
                            if (k.kompartemen_id && k.nama) {
                                kompartemenSelect.append(
                                    `<option value="${k.kompartemen_id}">${k.nama}</option>`);
                            }
                        });
                    }
                });

                // Populate Departemen when Kompartemen changes
                $('#kompartemen_id').on('change', function() {
                    let companyId = $('#company_id').val();
                    let kompartemenId = $(this).val();
                    let departemenSelect = $('#departemen_id');
                    departemenSelect.html('<option value="">-- Select Departemen --</option>');

                    let company = masterData.find(c => c.company_id === companyId);
                    if (company && company.kompartemen) {
                        let kompartemen = company.kompartemen.find(k => k.kompartemen_id === kompartemenId);
                        if (kompartemen && kompartemen.departemen) {
                            kompartemen.departemen.forEach(function(d) {
                                if (d.departemen_id && d.nama) {
                                    departemenSelect.append(
                                        `<option value="${d.departemen_id}">${d.nama}</option>`);
                                }
                            });
                        }
                    }
                });

                // AJAX check for unique number
                $('#number').on('blur', function() {
                    let number = $(this).val();
                    $.get('{{ route('penomoran-uam.checkNumber') }}', {
                        number: number
                    }, function(data) {
                        $('#number-error').text(data.exists ? 'Number already exists!' : '');
                    });
                });
            });
        </script>
    @endsection
