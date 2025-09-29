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

        <h1>Edit Penomoran UAM</h1>
        <form action="{{ route('penomoran-uam.update', $penomoranUAM->id) }}" method="POST">
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
                <label for="kompartemen_id">Kompartemen</label> {{ $selectedKompartemen }}
                <select id="kompartemen_id" name="kompartemen_id" class="form-control">
                    <option value="">-- Select Kompartemen --</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="departemen_id">Departemen</label> {{ $selectedDepartemen }}
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
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mb-3">Update</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('penomoran-uam.index') }}" class="btn btn-secondary">Kembali ke Index</a>
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

        const findCompany = companyId => organizationData.find(c => String(c.company_code) === String(companyId));

        const populateKompartemen = (companyId, selectedId = '') => {
            const $select = $('#kompartemen_id');
            $select.html('<option value="">-- Select Kompartemen --</option>');
            const company = findCompany(companyId);
            if (!company) return;

            (company.kompartemen || []).forEach(kom => {
                $select.append(
                    `<option value="${kom.kompartemen_id}" ${String(kom.kompartemen_id) === String(selectedId) ? 'selected' : ''}>${kom.nama}</option>`
                );
            });
        };

        const populateDepartemen = (companyId, kompartemenId = '', selectedId = '') => {
            const $select = $('#departemen_id');
            $select.html('<option value="">-- Select Departemen --</option>');
            const company = findCompany(companyId);
            if (!company) return;

            if (kompartemenId) {
                const kompartemen = (company.kompartemen || []).find(k => String(k.kompartemen_id) === String(
                    kompartemenId));
                (kompartemen?.departemen || []).forEach(dep => {
                    $select.append(
                        `<option value="${dep.departemen_id}" ${String(dep.departemen_id) === String(selectedId) ? 'selected' : ''}>${dep.nama}</option>`
                    );
                });
            }

            (company.departemen_without_kompartemen || []).forEach(dep => {
                const label = `${dep.nama} (Tanpa Kompartemen)`;
                $select.append(
                    `<option value="${dep.departemen_id}" ${String(dep.departemen_id) === String(selectedId) ? 'selected' : ''}>${label}</option>`
                );
            });
        };

        $(function() {
            const companyId = selectedCompany || $('#company_id').val();

            if (companyId) {
                $('#company_id').val(companyId);
                populateKompartemen(companyId, selectedKompartemen);
                populateDepartemen(companyId, selectedKompartemen, selectedDepartemen);
            }

            $('#company_id').on('change', function() {
                const current = $(this).val();
                populateKompartemen(current);
                populateDepartemen(current);
            });

            $('#kompartemen_id').on('change', function() {
                const currentCompany = $('#company_id').val();
                populateDepartemen(currentCompany, $(this).val());
            });

            $('#number').on('blur', function() {
                $.get('{{ route('penomoran-uam.checkNumber') }}', {
                    number: $(this).val(),
                    except: {{ $penomoranUAM->id }}
                }, function(data) {
                    $('#number-error').text(data.exists ? 'Number already exists!' : '');
                });
            });
        });
    </script>
@endsection
