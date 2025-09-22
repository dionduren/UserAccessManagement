@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Periode</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form action="{{ route('periode.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="periode" class="form-label">Periode</label>
                <input type="text" class="form-control" name="definisi" required>
            </div>

            <div class="mb-3">
                <label for="tanggal_create_periode" class="form-label">Tanggal Buat Periode</label>
                <input type="datetime-local" id="tanggal_create_periode"
                    class="form-control @error('tanggal_create_periode') is-invalid @enderror" name="tanggal_create_periode"
                    value="{{ old('tanggal_create_periode', \Carbon\Carbon::now()->format('Y-m-d\TH:i')) }}" required>
                @error('tanggal_create_periode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Buat Periode</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script></script>
@endsection
