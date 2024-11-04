@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Kompartemen</h1>

        <form action="{{ route('kompartemens.update', $kompartemen->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Company Dropdown -->
            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" class="form-control" required>
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ $company->id == $kompartemen->company_id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Kompartemen Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Kompartemen Name</label>
                <input type="text" class="form-control" name="name" value="{{ $kompartemen->name }}" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description">{{ $kompartemen->description }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Kompartemen</button>
        </form>
    </div>
@endsection
