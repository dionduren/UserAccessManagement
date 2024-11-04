@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Single Role</h1>

        <form action="{{ route('single-roles.update', $singleRole) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Single Role Name</label>
                <input type="text" class="form-control" name="name" value="{{ $singleRole->name }}" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description">{{ $singleRole->description }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Single Role</button>
        </form>
    </div>
@endsection
