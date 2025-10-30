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
                <div class="input-group">
                    <select name="job_role_id" id="job_role_id" class="form-control" required>
                        <option value="">Pilih Job Role</option>
                        @foreach ($jobRoles as $jobRole)
                            <option value="{{ $jobRole->job_role_id }}" data-nama="{{ $jobRole->nama }}"
                                {{ $nikJobRole->job_role_id == $jobRole->job_role_id ? 'selected' : '' }}>
                                {{ $jobRole->job_role_id }} - {{ $jobRole->nama }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="editJobRoleBtn" class="btn btn-outline-primary"
                        {{ $nikJobRole->job_role_id ? '' : 'disabled' }}>
                        <i class="fas fa-edit"></i> Edit Job Role
                    </button>
                </div>
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
        // Determine if the user is from company A000 to show all job roles
        window.isSuper =
            {{ auth()->check() && optional(auth()->user()->loginDetail)->company_code === 'A000' ? 'true' : 'false' }};

        $(document).ready(function() {
            $('#job_role_id, #user_generic_id, #periode_id').select2({});

            // Store initial job_role_id to restore if available after reload
            let initialJobRoleId = '{{ $nikJobRole->job_role_id }}';

            function sortAndFilterJobRoles() {
                const $sel = $('#job_role_id');
                // preserve current/initial selection
                const selectedVal = $sel.val() || initialJobRoleId;

                const options = [];
                $sel.find('option').each(function(idx) {
                    if (idx === 0) return; // skip placeholder
                    const val = ($(this).attr('value') ?? '').trim();
                    const text = ($(this).text() ?? '').trim();
                    options.push({
                        val,
                        text,
                        hasId: val !== '' // empty value means no job_role_id
                    });
                });

                // Separate with and without job_role_id
                let withId = options.filter(o => o.hasId);
                let withoutId = options.filter(o => !o.hasId);

                // Sort both lists alphabetically by text
                withId.sort((a, b) => a.text.localeCompare(b.text));
                withoutId.sort((a, b) => a.text.localeCompare(b.text));

                // for non-A000 users, drop "withoutId"
                const finalOptions = window.isSuper ? withId.concat(withoutId) : withId;

                // Rebuild dropdown (preserve first placeholder)
                const placeholder = $sel.find('option').first().clone();
                $sel.empty().append(placeholder);
                finalOptions.forEach(o => {
                    $sel.append(new Option(o.text, o.val, false, false));
                });

                // Restore selection if still available
                if (selectedVal && $sel.find(`option[value="${selectedVal}"]`).length) {
                    $sel.val(selectedVal).trigger('change');
                } else {
                    $sel.val('').trigger('change');
                }
            }

            function loadJobRolesByPeriode(periodeId) {
                if (!periodeId) {
                    $('#job_role_id').empty().append('<option value="">Pilih Periode terlebih dahulu</option>');
                    return;
                }

                $.ajax({
                    url: '/api/master-data/job-roles-by-periode',
                    method: 'GET',
                    data: {
                        periode_id: periodeId
                    },
                    success: function(data) {
                        const $sel = $('#job_role_id');
                        const currentVal = $sel.val() || initialJobRoleId;

                        $sel.empty().append('<option value="">Pilih Job Role</option>');

                        // Filter by isSuper
                        let filtered = window.isSuper ? data : data.filter(jr => jr.job_role_id);

                        // Sort: with job_role_id first, then by name
                        filtered.sort((a, b) => {
                            if (!a.job_role_id && b.job_role_id) return 1;
                            if (a.job_role_id && !b.job_role_id) return -1;
                            return a.nama.localeCompare(b.nama);
                        });

                        filtered.forEach(jr => {
                            const displayText = jr.job_role_id ?
                                `${jr.job_role_id} - ${jr.nama}` : jr.nama;
                            $sel.append(new Option(displayText, jr.job_role_id, false, false));
                        });

                        // Restore selection if available
                        if (currentVal && $sel.find(`option[value="${currentVal}"]`).length) {
                            $sel.val(currentVal).trigger('change');
                        } else {
                            $sel.val('').trigger('change');
                        }
                    },
                    error: function() {
                        alert('Failed to load job roles for this periode');
                    }
                });
            }

            // Load job roles when periode changes
            $('#periode_id').on('change', function() {
                const periodeId = $(this).val();
                loadJobRolesByPeriode(periodeId);
            });

            // Initial load
            sortAndFilterJobRoles();

            // job role edit button logic
            $('#job_role_id').on('change', function() {
                const selectedJobRoleId = $(this).val();
                const editBtn = $('#editJobRoleBtn');

                if (selectedJobRoleId) {
                    editBtn.prop('disabled', false);
                    editBtn.data('job-role-id', selectedJobRoleId);
                } else {
                    editBtn.prop('disabled', true);
                    editBtn.removeData('job-role-id');
                }

                // Update job_role_name field (existing functionality)
                var nama = $(this).find('option:selected').data('nama') || $(this).find('option:selected')
                    .text().split(' - ').slice(1).join(' - ');
                $('#job_role_name').val(nama || '');
            });

            $('#editJobRoleBtn').on('click', function() {
                const jobRoleId = $(this).data('job-role-id');
                if (jobRoleId) {
                    // open in new tab to preserve current form data
                    window.open(`{{ route('job-roles.index') }}/${jobRoleId}/edit`, '_blank');
                }
            });

            // set initial state on page load
            $('#job_role_id').trigger('change');
        });
    </script>
@endsection
