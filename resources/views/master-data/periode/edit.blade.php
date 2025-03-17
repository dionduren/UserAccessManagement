@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Periode</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('periode.update', $periode->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="definisi" class="form-label">Periode</label>
                <input type="text" class="form-control" name="definisi" value="{{ $periode->definisi }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Ubah Periode</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script></script>
@endsection
