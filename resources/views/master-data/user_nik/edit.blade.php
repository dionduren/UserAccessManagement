@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit User NIK</h1>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('user-nik.update', ['user_nik' => $userNIK->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="user_code">NIK</label>
                <input type="text" name="user_code" id="user_code" value="{{ $userNIK->user_code }}" class="form-control">
            </div>

            <div class="form-group">
                <label for="user_detail_info">User Detail</label>
                <textarea class="form-control" id="user_detail_info" disabled rows="9"></textarea>
                <button type="button" id="checkUserDetailBtn" class="btn btn-primary mt-2">Check User Detail</button>
            </div>


            <div class="form-group">
                <label for="license_type">Tipe Lisensi</label>
                <select name="license_type" id="license_type"
                    class="form-control form-select @error('license_type') is-invalid @enderror">
                    <option value="">-- Pilih Tipe Lisensi --</option>
                    <option value="CA" {{ $userNIK->license_type == 'CA' ? 'selected' : '' }}>CA - SAP Application
                        Developer</option>
                    <option value="CB" {{ $userNIK->license_type == 'CB' ? 'selected' : '' }}>CB - SAP Application
                        Professional</option>
                </select>
                @error('license_type')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="periode_id">Periode</label>
                <select name="periode_id" id="periode_id"
                    class="form-control form-select @error('periode_id') is-invalid @enderror">
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}" {{ $userNIK->periode_id == $periode->id ? 'selected' : '' }}>
                            {{ $periode->definisi }}
                        </option>
                    @endforeach
                </select>
                @error('periode_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="valid_from">Valid From</label>
                <input type="date" name="valid_from" id="valid_from"
                    value="{{ old('valid_from', $userNIK->valid_from ? date('Y-m-d', strtotime($userNIK->valid_from)) : null) }}"
                    class="form-control @error('valid_from') is-invalid @enderror">
                @error('valid_from')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="valid_to">Valid To</label>
                <input type="date" name="valid_to" id="valid_to"
                    value="{{ old('valid_to', $userNIK->valid_to ? date('Y-m-d', strtotime($userNIK->valid_to)) : null) }}"
                    class="form-control @error('valid_to') is-invalid @enderror">
                @error('valid_to')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary mt-3">Update</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const checkUserDetail = function() {
                const checkUserDetailUrl =
                    "{{ route('user-nik.check-user-detail', ['user_code' => '__userCode__']) }}";
                const userCode = $('#user_code').val();
                const url = checkUserDetailUrl.replace('__userCode__', encodeURIComponent(userCode));
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        const userDetail = response.userDetail; // access nested property here
                        const userDetailInfo =
                            `Nama: ${userDetail.nama ?? 'N/A'}\n` +
                            `Perusahaan: ${userDetail.company.nama ?? 'N/A'}\n` +
                            `Direktorat: ${userDetail.direktorat.direktorat ?? 'N/A'}\n` +
                            `Kompartemen: ${userDetail.kompartemen.nama ?? 'N/A'}\n` +
                            `Departemen: ${userDetail.departemen.nama ?? 'N/A'}\n` +
                            `Cost Center: ${userDetail.cost_center ?? 'N/A'}\n`;
                        // `Jabatan: ${userDetail.jabatan ?? 'N/A'}\n` +
                        // `Email: ${userDetail.email ?? 'N/A'}`;
                        $('#user_detail_info').val(userDetailInfo);
                    },
                    error: function(xhr) {
                        console.error('Error fetching user details:', xhr.statusText);
                        $('#user_detail_info').val(`${xhr.responseJSON.message}`);
                    }
                });
            };

            checkUserDetail();
            $('#checkUserDetailBtn').click(checkUserDetail);
        });
    </script>
@endsection
