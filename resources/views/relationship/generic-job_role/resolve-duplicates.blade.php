@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Perbaiki Duplikat Job Role - {{ $userGeneric->user_code }}</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="alert alert-info">
                    <strong>User:</strong> {{ $userGeneric->user_code }} - {{ $userGeneric->user_profile }}<br>
                    <strong>Periode Saat Ini:</strong> {{ $duplicateRecords->first()->periode->definisi }}<br>
                    <strong>Jumlah Job Roles:</strong> {{ $duplicateRecords->count() }}
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> User ini memiliki {{ $duplicateRecords->count() }} job roles dalam periode
                    yang sama.
                    Anda harus memindahkan job roles ke periode yang berbeda atau menghapus yang tidak diperlukan.
                </div>

                <form action="{{ route('user-generic-job-role.duplicates.split', $userGeneric->user_code) }}"
                    method="POST">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID NIKJobRole</th>
                                    <th>ID Job Role</th>
                                    <th>Nama Job Role</th>
                                    <th>Periode Saat Ini</th>
                                    <th>Aksi</th>
                                    <th>Periode Baru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($duplicateRecords as $index => $record)
                                    <tr>
                                        <td>{{ $record->id }}</td>
                                        <td>{{ $record->job_role_id }}</td>
                                        <td>{{ $record->jobRole?->nama ?? 'N/A' }}</td>
                                        <td>{{ $record->periode->definisi }}</td>
                                        <td>
                                            <input type="hidden" name="assignments[{{ $index }}][record_id]"
                                                value="{{ $record->id }}">

                                            <select name="assignments[{{ $index }}][action]"
                                                class="form-control action-select" data-index="{{ $index }}"
                                                required>
                                                <option value="keep">Tetap di Periode Saat Ini</option>
                                                <option value="update">Pindah ke Periode Lain</option>
                                                <option value="delete">Hapus Assignment Ini</option>
                                            </select>
                                        </td>
                                        <td>
                                            <!-- Hidden input to ensure periode_id is always submitted for "keep" -->
                                            <input type="hidden" name="assignments[{{ $index }}][periode_id]"
                                                id="periode-hidden-{{ $index }}" value="{{ $periodeId }}">

                                            <select name="assignments[{{ $index }}][periode_id]"
                                                class="form-control periode-select" id="periode-{{ $index }}"
                                                disabled>
                                                <option value="">-- Pilih Periode --</option>
                                                @foreach ($periodes as $periode)
                                                    <option value="{{ $periode->id }}"
                                                        {{ $periode->id == $periodeId ? 'selected' : '' }}>
                                                        {{ $periode->definisi }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Panduan:</strong>
                        <ul class="mb-0">
                            <li><strong>Tetap di Periode Saat Ini:</strong> Biarkan job role tetap di periode saat ini
                                (hanya 1 yang boleh dipilih untuk tetap)</li>
                            <li><strong>Pindah ke Periode Lain:</strong> Pindahkan job role ke periode yang berbeda</li>
                            <li><strong>Hapus Assignment Ini:</strong> Hapus assignment job role ini secara permanen</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('user-generic-job-role.index') }}" class="btn btn-secondary">
                            <i class="bi bi-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.action-select').on('change', function() {
                const index = $(this).data('index');
                const action = $(this).val();
                const periodeSelect = $(`#periode-${index}`);
                const periodeHidden = $(`#periode-hidden-${index}`);

                if (action === 'update') {
                    // Enable dropdown for manual selection
                    periodeSelect.prop('disabled', false).prop('required', true);
                    periodeHidden.prop('disabled', true); // Disable hidden input
                } else if (action === 'keep') {
                    // Keep current periode
                    periodeSelect.prop('disabled', true).prop('required', false);
                    periodeSelect.val('{{ $periodeId }}'); // Set to current periode
                    periodeHidden.val('{{ $periodeId }}').prop('disabled',
                    false); // Enable hidden input
                } else if (action === 'delete') {
                    // No periode needed for delete
                    periodeSelect.prop('disabled', true).prop('required', false);
                    periodeHidden.prop('disabled', true);
                }
            });

            // Trigger initial state for all dropdowns
            $('.action-select').trigger('change');
        });
    </script>
@endsection
