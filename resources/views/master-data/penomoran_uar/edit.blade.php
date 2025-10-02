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

        <h1>Edit Penomoran UAR</h1>
        <form action="{{ route('penomoran-uar.update', $penomoranUAR->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="company_id">Company</label>
                <select id="company_id" name="company_id" class="form-control" required>
                    <option value="">-- Select Company --</option>
                    @foreach ($companySet as $company)
                        <option value="{{ $company['company_code'] }}"
                            {{ $selectedCompany == $company['company_code'] ? 'selected' : '' }}>
                            {{ $company['nama'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="kompartemen_id">Kompartemen</label>
                <select id="kompartemen_id" name="kompartemen_id" class="form-control">
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
                    value="{{ old('number', $penomoranUAR->number ?? '') }}" required>
                <span id="number-error" class="text-danger"></span>
            </div>

            <div class="row">
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mb-3">Update</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('penomoran-uar.index') }}" class="btn btn-secondary">Kembali ke Index</a>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        const organizationData = @json($organizationData);
        const selectedCompany = "{{ $selectedCompany }}";
        const selectedKompartemen = "{{ $selectedKompartemen }}";
        const selectedDepartemen = "{{ $selectedDepartemen }}";

        const findCompany = companyId =>
            organizationData.find(c => String(c.company_code) === String(companyId));

        const populateKompartemen = (companyId, komId = '') => {
            const $sel = $('#kompartemen_id');
            $sel.html('<option value="">-- Select Kompartemen --</option>');

            const company = findCompany(companyId);
            if (!company) return;

            (company.kompartemen || []).forEach(kom => {
                $sel.append(`<option value="${kom.kompartemen_id}" ${String(kom.kompartemen_id) === String(komId) ? 'selected' : ''}>
                    ${kom.nama}
                </option>`);
            });
        };

        const populateDepartemen = (companyId, komId = '', depId = '') => {
            const $sel = $('#departemen_id');
            $sel.html('<option value="">-- Select Departemen --</option>');

            const company = findCompany(companyId);
            if (!company) return;

            if (komId) {
                const kom = (company.kompartemen || []).find(k => String(k.kompartemen_id) === String(komId));
                (kom?.departemen || []).forEach(dep => {
                    $sel.append(`<option value="${dep.departemen_id}" ${String(dep.departemen_id) === String(depId) ? 'selected' : ''}>
                        ${dep.nama}
                    </option>`);
                });
            }

            (company.departemen_without_kompartemen || []).forEach(dep => {
                const label = `${dep.nama} (Tanpa Kompartemen)`;
                $sel.append(`<option value="${dep.departemen_id}" ${String(dep.departemen_id) === String(depId) ? 'selected' : ''}>
                    ${label}
                </option>`);
            });
        };

        $(function() {
            const initialCompany = $('#company_id').val() || selectedCompany;

            if (initialCompany) {
                $('#company_id').val(initialCompany);
                populateKompartemen(initialCompany, selectedKompartemen);
                populateDepartemen(initialCompany, selectedKompartemen, selectedDepartemen);
            }

            $('#company_id').on('change', function() {
                const companyId = $(this).val();
                populateKompartemen(companyId);
                populateDepartemen(companyId);
            });

            $('#kompartemen_id').on('change', function() {
                const companyId = $('#company_id').val();
                populateDepartemen(companyId, $(this).val());
            });

            $('#number').on('blur', function() {
                $.get('{{ route('penomoran-uar.checkNumber') }}', {
                    number: $(this).val(),
                    except: {{ $penomoranUAR->id }},
                    company_id: $('#company_id').val()
                }, data => $('#number-error').text(data.exists ? 'Nomor sudah digunakan oleh ' +
                    data
                    .unit_kerja_id + '!' : ''))
            });
        });
    </script>
@endsection
