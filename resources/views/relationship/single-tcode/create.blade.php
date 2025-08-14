@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Buat Relationship antara Single Role dan Single Tcode</h1>

        <!-- Error Display -->
        @if (session('validationErrors') || session('error'))
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
                    <!-- Validation Errors -->
                    @if (session('validationErrors'))
                        @foreach (session('validationErrors') as $row => $messages)
                            <li>Row {{ $row }}:
                                <ul>
                                    @foreach ($messages as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    @endif

                    <!-- General Error -->
                    @if (session('error'))
                        <li>{{ session('error') }}</li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Laravel Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('single-tcode.store') }}" method="POST">
            @csrf

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control select2" required>
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->company_code }}">{{ $company->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Single Role Dropdown -->
            <div class="mb-3">
                <label for="single_role_id" class="form-label">Single Role</label>
                <select name="single_role_id" id="single_role_id" class="form-control select2" required>
                    <option value="">Pilih Single Role</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="tcode_id" class="form-label">Tcodes</label>
                <select name="tcode_id[]" multiple class="form-control select2" required>
                    @foreach ($tcodes as $tcode)
                        <option value="{{ $tcode->code }}">
                            {{ $tcode->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Relationship</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                allowClear: true,
                width: '100%'
            });

            // Function to populate Single Roles and Single Tcodes based on selected Company
            function populateRoles(companyId) {
                $('#single_role_id').empty().append(
                    '<option value="">Pilih Single Role</option>');

                $('#single_tcode_id').empty().append(
                    '<option value="">Pilih Single Tcode</option>');

                // This route and function need to be defined in the SingleTcodeController
                $.get('{{ route('single-tcode.filter-company') }}', {
                    company_id: companyId
                }, function(data) {
                    $.each(data.singleRoles, function(index, singleRole) {
                        $('#single_role_id').append(
                            $('<option>').val(singleRole.id).text(singleRole.nama)
                        );
                    });

                    $.each(data.singleTcodes, function(index, singleTcode) {
                        $('#single_tcode_id').append(
                            $('<option>').val(singleTcode.id).text(singleTcode.nama)
                        );
                    });
                });

                $('#single_role_id').select2({
                    width: '100%',
                    allowClear: true,
                });

                $('#single_tcode_id').select2({
                    width: '100%',
                    allowClear: true,
                });
            }

            // Populate Single Roles and Single Tcodes based on selected Company
            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateRoles(companyId);
            });
        });
    </script>
@endsection
