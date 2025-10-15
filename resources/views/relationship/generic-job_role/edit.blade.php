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
        // Whether current user is A000 (can see roles without job_role_id)
        window.isSuper =
            {{ auth()->check() && optional(auth()->user()->loginDetail)->company_code === 'A000' ? 'true' : 'false' }};

        $(document).ready(function() {
            $('#job_role_id, #user_generic_id, #periode_id').select2({});

            function sortAndFilterJobRoles() {
                const $sel = $('#job_role_id');
                // preserve current/initial selection
                const selectedVal = $sel.val() || '{{ $nikJobRole->job_role_id }}';

                const options = [];
                $sel.find('option').each(function(idx) {
                    // keep placeholder at index 0
                    if (idx === 0) return;
                    const val = ($(this).attr('value') ?? '').trim();
                    const text = ($(this).text() ?? '').trim();
                    options.push({
                        val,
                        text,
                        hasId: val !== '' // empty value means no job_role_id
                    });
                });

                // split by presence of job_role_id
                let withId = options.filter(o => o.hasId);
                let withoutId = options.filter(o => !o.hasId);

                // sort by name within each group
                withId.sort((a, b) => a.text.localeCompare(b.text));
                withoutId.sort((a, b) => a.text.localeCompare(b.text));

                // for non-A000 users, drop "withoutId"
                const finalOptions = window.isSuper ? withId.concat(withoutId) : withId;

                // rebuild dropdown (preserve first placeholder)
                const placeholder = $sel.find('option').first().clone();
                $sel.empty().append(placeholder);
                finalOptions.forEach(o => {
                    $sel.append(new Option(o.text, o.val, false, false));
                });

                // restore selection if still available
                if (selectedVal && $sel.find(`option[value="${selectedVal}"]`).length) {
                    $sel.val(selectedVal).trigger('change');
                } else if (!window.isSuper && (!selectedVal || selectedVal === '')) {
                    // selected was a no-id role but user is not A000; leave unselected
                    $sel.val('').trigger('change');
                }
            }

            // run once on load and whenever the dropdown opens (defense in depth)
            sortAndFilterJobRoles();
            $('#job_role_id').on('select2:open', sortAndFilterJobRoles);

            // keep the name box synced
            $('#job_role_id').on('change', function() {
                var nama = $(this).find('option:selected').data('nama') || $(this).find('option:selected')
                    .text().split(' - ').slice(1).join(' - ');
                $('#job_role_name').val(nama || '');
            });
            $('#job_role_id').trigger('change');
        });
    </script>
@endsection
