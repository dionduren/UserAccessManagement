@extends('layouts.app')

@section('content')
    <h1>Departemen Details</h1>
    <p>Name: { $departemen->name }</p>
    <p>Description: { $departemen->description }</p>
    <a href="{ route('departemen.edit', $departemen->id) }">Edit</a> |
    <form action="{ route('departemen.destroy', $departemen->id) }" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form> |
    <a href="{ route('departemen.index') }">Back to List</a>
@endsection