@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Departemen</h1>

        <form action="{{ route('departemens.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kompartemen</label>
                <select name="kompartemen_id" class="form-control" required>
                    <option value="">Select a kompartemen</option>
                    @foreach ($kompartemens as $kompartemen)
                        <option value="{{ $kompartemen->id }}">{{ $kompartemen->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Departemen Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Departemen</button>
        </form>
    </div>
@endsection
