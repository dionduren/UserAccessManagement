@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Departemens</h1>
        <a href="{{ route('departemens.create') }}" class="btn btn-primary mb-3">Create New Departemen</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Kompartemen</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departemens as $departemen)
                    <tr>
                        <td>{{ $departemen->kompartemen->name }}</td>
                        <td>{{ $departemen->name }}</td>
                        <td>{{ $departemen->description }}</td>
                        <td>
                            <a href="{{ route('departemens.edit', $departemen) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('departemens.destroy', $departemen) }}" method="POST"
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
