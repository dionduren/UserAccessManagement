@extends('layouts.app')

@section('content')
    <h1>Kompartemen Details</h1>
    <p>Name: { $kompartemen->name }</p>
    <p>Description: { $kompartemen->description }</p>
    <a href="{ route('kompartemen.edit', $kompartemen->id) }">Edit</a> |
    <form action="{ route('kompartemen.destroy', $kompartemen->id) }" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form> |
    <a href="{ route('kompartemen.index') }">Back to List</a>
@endsection