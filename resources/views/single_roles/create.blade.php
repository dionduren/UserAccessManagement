@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Single Role</h1>

        <form action="{{ route('single-roles.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Single Role Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Single Role</button>
        </form>
    </div>
@endsection
