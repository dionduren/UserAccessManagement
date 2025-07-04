@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Tambah Relasi User Generic - Job Role</h2>
        <form action="{{ route('user-generic-job-role.store') }}" method="POST">
            @csrf

            <div class="form-group mb-3">
                <label for="periode_id">Periode</label>
                <select name="periode_id" id="periode_id" class="form-control" required>
                    <option value="">Pilih Periode</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->definisi }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="user_generic_id">User Generic</label>
                <select name="user_generic_id" id="user_generic_id" class="form-control" required>
                    <option value="">Pilih User Generic</option>
                    @foreach ($userGenerics as $user)
                        <option value="{{ $user->id }}" data-kompartemen="{{ $user->kompartemen_name ?? 'null' }}"
                            data-departemen="{{ $user->departemen_name ?? 'null' }}"
                            data-jobrole="{{ $user->job_role_name ?? 'null' }}">
                            {{ $user->user_code }} - {{ $user->group }} - {{ $user->unit_kerja }} -
                            {{ $user->job_role_name }} - {{ $user->pic }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="job_role_id">Job Role</label>
                <select name="job_role_id" id="job_role_id" class="form-control" required>
                    <option value="">Pilih Job Role</option>
                    @foreach ($jobRoles as $jobRole)
                        <option value="{{ $jobRole->job_role_id }}" data-nama="{{ $jobRole->nama }}">
                            {{ $jobRole->job_role_id }} - {{ $jobRole->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="job_role_name">Job Role Name</label>
                <input type="text" name="job_role_name" id="job_role_name" class="form-control" required readonly>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('user-generic-job-role.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize select2 for better UX
            $('#user_generic_id, #periode_id, #job_role_id').select2({
                placeholder: 'Pilih',
                allowClear: true
            });

            // Set job_role_name when job_role_id changes
            $('#job_role_id').on('change', function() {
                var nama = $(this).find('option:selected').data('nama') || '';
                $('#job_role_name').val(nama);
            });

            // Trigger change on page load if needed
            $('#user_generic_id').trigger('change');
        });
    </script>
@endsection
