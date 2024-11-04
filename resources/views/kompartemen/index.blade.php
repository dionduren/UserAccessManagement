@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Kompartemens</h1>
        <a href="{{ route('kompartemens.create') }}" class="btn btn-primary mb-3">Create New Kompartemen</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kompartemens as $kompartemen)
                    <tr>
                        <td>{{ $kompartemen->company->name }}</td>
                        <td>{{ $kompartemen->name }}</td>
                        <td>{{ $kompartemen->description }}</td>
                        <td>
                            <a href="{{ route('kompartemens.edit', $kompartemen) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('kompartemens.destroy', $kompartemen) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
