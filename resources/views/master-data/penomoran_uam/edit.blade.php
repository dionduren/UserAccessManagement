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
                    @foreach ($companies as $company)
                        <option value="{{ $company['company_id'] }}"
                            {{ $selectedCompany == $company['company_id'] ? 'selected' : '' }}>
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
        let masterData = @json($masterData);
        let selectedCompany = "{{ $selectedCompany }}";
        let selectedKompartemen = "{{ $selectedKompartemen }}";
        let selectedDepartemen = "{{ $selectedDepartemen }}";

        $(document).ready(function() {
            // Populate Kompartemen
            function populateKompartemen(companyId, selectedKompartemen) {
                let kompartemenSelect = $('#kompartemen_id');
                kompartemenSelect.html('<option value="">-- Select Kompartemen --</option>');
                let company = masterData.find(c => c.company_id === companyId);
                if (company && company.kompartemen) {
                    company.kompartemen.forEach(function(k) {
                        if (k.kompartemen_id && k.nama) {
                            kompartemenSelect.append(
                                `<option value="${k.kompartemen_id}" ${selectedKompartemen == k.kompartemen_id ? 'selected' : ''}>${k.nama}</option>`
                            );
                        }
                    });
                }
            }

            // Populate Departemen
            function populateDepartemen(companyId, kompartemenId, selectedDepartemen) {
                let departemenSelect = $('#departemen_id');
                departemenSelect.html('<option value="">-- Select Departemen --</option>');
                let company = masterData.find(c => c.company_id === companyId);
                if (company && company.kompartemen) {
                    let kompartemen = company.kompartemen.find(k => k.kompartemen_id === kompartemenId);
                    if (kompartemen && kompartemen.departemen) {
                        kompartemen.departemen.forEach(function(d) {
                            if (d.departemen_id && d.nama) {
                                departemenSelect.append(
                                    `<option value="${d.departemen_id}" ${selectedDepartemen == d.departemen_id ? 'selected' : ''}>${d.nama}</option>`
                                );
                            }
                        });
                    }
                }
            }

            // Initial population
            if (selectedCompany) {
                populateKompartemen(selectedCompany, selectedKompartemen);
            }
            if (selectedCompany && selectedKompartemen) {
                populateDepartemen(selectedCompany, selectedKompartemen, selectedDepartemen);
            }

            // On change events
            $('#company_id').on('change', function() {
                populateKompartemen($(this).val(), '');
                $('#departemen_id').html('<option value="">-- Select Departemen --</option>');
            });

            $('#kompartemen_id').on('change', function() {
                populateDepartemen($('#company_id').val(), $(this).val(), '');
            });

            // AJAX check for unique number
            $('#number').on('blur', function() {
                let number = $(this).val();
                $.get('{{ route('penomoran-uam.checkNumber') }}', {
                    number: number,
                    except: {{ $penomoranUAM->id }}
                }, function(data) {
                    $('#number-error').text(data.exists ? 'Number already exists!' : '');
                });
            });
        });
    </script>
@endsection
