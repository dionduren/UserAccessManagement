@extends('layouts.app')

@section('content')
    <h1>Single_roles Details</h1>
    <p>Name: { $single_roles->name }</p>
    <p>Description: { $single_roles->description }</p>
    <a href="{ route('single_roles.edit', $single_roles->id) }">Edit</a> |
    <form action="{ route('single_roles.destroy', $single_roles->id) }" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form> |
    <a href="{ route('single_roles.index') }">Back to List</a>
@endsection