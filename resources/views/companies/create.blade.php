@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Company</h1>

        <form action="{{ route('companies.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="company_code" class="form-label">Company Code</label>
                <input type="text" class="form-control" name="company_code" required>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Company Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label for="shortname" class="form-label">Singkatan</label>
                <input type="text" class="form-control" name="shortname" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Company</button>
        </form>
    </div>
@endsection
