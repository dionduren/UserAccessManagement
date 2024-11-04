@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Kompartemen</h1>

        <form action="{{ route('kompartemens.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" class="form-control" required>
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Kompartemen Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Kompartemen</button>
        </form>
    </div>
@endsection
