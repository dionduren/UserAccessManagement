@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Departemen</h1>

        <form action="{{ route('departemens.update', $departemen) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Kompartemen Dropdown -->
            <div class="mb-3">
                <label for="kompartemen_id" class="form-label">Kompartemen</label>
                <select name="kompartemen_id" class="form-control" required>
                    <option value="">Select a kompartemen</option>
                    @foreach ($kompartemens as $kompartemen)
                        <option value="{{ $kompartemen->id }}"
                            {{ $kompartemen->id == $departemen->kompartemen_id ? 'selected' : '' }}>
                            {{ $kompartemen->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Departemen Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Departemen Name</label>
                <input type="text" class="form-control" name="name" value="{{ $departemen->name }}" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description">{{ $departemen->description }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Departemen</button>
        </form>
    </div>
@endsection
