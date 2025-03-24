@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Show User Job Role</h1>
        <div class="card">
            <div class="card-header">
                Job Role Details
            </div>
            <div class="card-body">
                <dl>
                    <dt>Periode</dt>
                    <dd>{{ $nikJobRole->periode->definisi ?? '-' }}</dd>

                    <dt>User</dt>
                    <dd>
                        {{ $nikJobRole->nik ?? '-' }} -
                        {{ $nikJobRole->userDetail->nama ?? 'Belum ada Data Karyawan' }}
                    </dd>

                    <dt>Job Role</dt>
                    <dd>{{ $nikJobRole->jobRole->nama_jabatan ?? '-' }}</dd>

                    <dt>Company</dt>
                    <dd>{{ $nikJobRole->jobRole->company->name ?? '-' }}</dd>

                    <dt>Kompartemen</dt>
                    <dd>{{ $nikJobRole->jobRole->kompartemen->name ?? '-' }}</dd>

                    <dt>Departemen</dt>
                    <dd>{{ $nikJobRole->jobRole->departemen->name ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        <a href="{{ route('nik-job.index') }}" class="btn btn-secondary mt-3">Back to List</a>
    </div>
@endsection
