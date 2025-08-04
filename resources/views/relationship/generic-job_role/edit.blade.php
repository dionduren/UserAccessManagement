@extends('layouts.app')

@section('content')
    @if (session('error'))
        <div class="alert alert-danger">
            <h4>Error:</h4>
            {{ session('error') }}
        </div>
    @endif

    <div class="container-fluid">
        <h2>Edit Relasi User Generic - Job Role</h2>
        <form action="{{ route('user-generic-job-role.update', $nikJobRole->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="user_generic_id">User Generic</label>
                <select name="user_generic_id" id="user_generic_id" class="form-control" required>
                    <option value="">Pilih User Generic</option>
                    @foreach ($userGenerics as $user)
                        <option value="{{ $user->user_code }}" {{ $nikJobRole->nik == $user->user_code ? 'selected' : '' }}>
                            {{ $user->user_code }} - {{ $user->user_profile ?? 'KOSONG' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="periode_id">Periode</label>
                <select name="periode_id" id="periode_id" class="form-control" required>
                    <option value="">Pilih Periode</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}"
                            {{ isset($nikJobRole->periode_id) && $nikJobRole->periode_id == $periode->id ? 'selected' : '' }}>
                            {{ $periode->definisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="job_role_id">Job Role</label>
                <select name="job_role_id" id="job_role_id" class="form-control" required>
                    <option value="">Pilih Job Role</option>
                    @foreach ($jobRoles as $jobRole)
                        <option value="{{ $jobRole->job_role_id }}" data-nama="{{ $jobRole->nama }}"
                            {{ $nikJobRole->job_role_id == $jobRole->job_role_id ? 'selected' : '' }}>
                            {{ $jobRole->job_role_id }} - {{ $jobRole->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="job_role_name">Job Role Name</label>
                <input type="text" name="job_role_name" id="job_role_name" class="form-control" required readonly
                    value="{{ $nikJobRole->jobRole->nama ?? '' }}">
            </div>

            <div class="form-group mb-3">
                <label for="flagged">Flagged</label>
                <select name="flagged" id="flagged" class="form-control">
                    <option value="0" {{ !$nikJobRole->flagged ? 'selected' : '' }}>Tidak</option>
                    <option value="1" {{ $nikJobRole->flagged ? 'selected' : '' }}>Ya</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="keterangan_flagged">Keterangan Flagged</label>
                <input type="text" name="keterangan_flagged" id="keterangan_flagged" class="form-control"
                    value="{{ $nikJobRole->keterangan_flagged }}">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('user-generic-job-role.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#job_role_id, #user_generic_id, #periode_id').select2({
                // placeholder: 'Pilih',
                // allowClear: true
            });

            // Set job_role_name when job_role_id changes
            $('#job_role_id').on('change', function() {
                var nama = $(this).find('option:selected').data('nama') || '';
                $('#job_role_name').val(nama);
            });

            // Trigger change on page load to set job_role_name
            $('#job_role_id').trigger('change');
        });
    </script>
@endsection
