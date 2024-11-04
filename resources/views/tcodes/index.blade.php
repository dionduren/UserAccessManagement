@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tcodes</h1>

        <a href="{{ route('tcodes.create') }}" class="btn btn-primary mb-3">Create New Tcode</a>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Company</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tcodes as $tcode)
                    <tr>
                        <td>{{ $tcode->code }}</td>
                        <td>{{ $tcode->deskripsi }}</td>
                        <td>{{ $tcode->company->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('tcodes.edit', $tcode) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('tcodes.destroy', $tcode) }}" method="POST" style="display:inline;">
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
