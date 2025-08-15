@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- General Error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Buat Relationship antara Composite Role dan Single Role</h2>
            </div>
            <div class="card-body">

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

                <form action="{{ route('composite-single.store') }}" method="POST">
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

                    <!-- Composite Role Dropdown -->
                    <div class="mb-3">
                        <label for="composite_role_id" class="form-label">Composite Role</label>
                        <select name="composite_role_id" id="composite_role_id" class="form-control select2" required>
                            <option value="">Pilih Composite Role</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="single_role_id" class="form-label">Single Roles</label>
                        <select name="single_role_id[]" id="single_role_id" class="form-control select2" multiple required>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Relationship</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                allowClear: true,
                width: '100%'
            });

            // Function to populate Composite Roles and Single Roles based on selected Company
            function populateRoles(companyId) {
                $('#composite_role_id').empty().append(
                    '<option value="">Pilih Composite Role</option>');

                $('#single_role_id').empty().append(
                    '<option value="">Pilih Single Role</option>');

                // This route and function need to be defined in the CompositeSingleController
                $.get('{{ route('composite-single.filter-company') }}', {
                    company_id: companyId
                }, function(data) {
                    $.each(data.compositeRoles, function(index, compositeRole) {
                        $('#composite_role_id').append(
                            $('<option>').val(compositeRole.id).text(compositeRole.nama)
                        );
                    });

                    $.each(data.singleRoles, function(index, singleRole) {
                        $('#single_role_id').append(
                            $('<option>').val(singleRole.id).text(singleRole.nama)
                        );
                    });
                });

                $('#composite_role_id').select2({
                    width: '100%',
                    allowClear: true,
                });

                $('#single_role_id').select2({
                    width: '100%',
                    allowClear: true,
                });
            }

            // Populate Composite Roles and Single Roles based on selected Company
            $('#company_id').change(function() {
                let companyId = $(this).val();
                populateRoles(companyId);
            });
        });
    </script>
@endsection
