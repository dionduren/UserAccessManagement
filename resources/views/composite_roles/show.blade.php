@extends('layouts.app')

@section('content')
    <h1>Composite_roles Details</h1>
    <p>Name: { $composite_roles->name }</p>
    <p>Description: { $composite_roles->description }</p>
    <a href="{ route('composite_roles.edit', $composite_roles->id) }">Edit</a> |
    <form action="{ route('composite_roles.destroy', $composite_roles->id) }" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form> |
    <a href="{ route('composite_roles.index') }">Back to List</a>
@endsection