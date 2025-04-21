@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Relationship Composite Role - Single Role</h1>

        <!-- Error Display -->
        @if (session('validationErrors') || session('error'))
            <div class="alert alert-danger">
                <h4>Error(s) occurred:</h4>
                <ul>
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

                    @if (session('error'))
                        <li>{{ session('error') }}</li>
                    @endif
                </ul>
            </div>
        @endif

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

        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('composite-single.update', $compositeSingle->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control select2" required disabled>
                    @foreach ($companies as $company)
                        <option value="{{ $company->company_code }}"
                            {{ $compositeSingle->company_id == $company->company_code ? 'selected' : '' }}>
                            {{ $company->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Composite Role Dropdown -->
            <div class="mb-3">
                <label for="composite_role_id" class="form-label">Composite Role</label>
                <select name="composite_role_id" id="composite_role_id" class="form-control select2" required>
                    @foreach ($compositeRoles as $role)
                        <option value="{{ $role->id }}"
                            {{ $compositeSingle->composite_role_id == $role->id ? 'selected' : '' }}>
                            {{ $role->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Multiple Single Role Dropdown -->
            <div class="mb-3">
                <label for="single_role_id" class="form-label">Single Roles</label>
                <select name="single_role_id[]" id="single_role_id" class="form-control select2" multiple required>
                    @foreach ($singleRoles as $role)
                        <option value="{{ $role->id }}"
                            {{ isset($selectedSingleRoles) && in_array($role->id, $selectedSingleRoles) ? 'selected' : '' }}>
                            {{ $role->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Relationship</button>
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
        });
    </script>
@endsection
