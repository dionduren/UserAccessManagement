@extends('layouts.app')

@section('content')
    <div class="container">

        @if (session('success'))
            <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $m)
                        <li>{{ $m }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Edit Relationship Single Tcode - Single Role</h2>
            </div>
            <div class="card-body">

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

                <form action="{{ route('single-tcode.update', $singleRole->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Single Single Role Dropdown -->
                    <div class="mb-3">
                        <label for="single_role_id" class="form-label">Single Roles</label>
                        <select name="single_role_id" id="single_role_id" class="form-control select2" required>

                            @foreach ($singleRoles as $role)
                                <option value="{{ $role->id }}" {{ $role->id == $singleRole->id ? 'selected' : '' }}>
                                    {{ $role->nama }}
                                </option>
                            @endforeach

                        </select>
                    </div>


                    <!-- Multiple Tcode Dropdown -->
                    <div class="mb-3">
                        <label for="single_tcode_id" class="form-label">Single Tcode</label>
                        <select name="tcode_id[]" id="single_tcode_id" class="form-control select2" multiple required>
                            @foreach ($tcodes as $tcode)
                                <option value="{{ $tcode->code }}"
                                    {{ in_array($tcode->code, $selectedTcodes) ? 'selected' : '' }}>
                                    {{ $tcode->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Relationship</button>
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
        });
    </script>
@endsection
