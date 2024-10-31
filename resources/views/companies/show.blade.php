@extends('layouts.app')

@section('content')
    <h1>Companies Details</h1>
    <p>Name: { $companies->name }</p>
    <p>Description: { $companies->description }</p>
    <a href="{ route('companies.edit', $companies->id) }">Edit</a> |
    <form action="{ route('companies.destroy', $companies->id) }" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form> |
    <a href="{ route('companies.index') }">Back to List</a>
@endsection