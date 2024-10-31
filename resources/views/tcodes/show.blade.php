@extends('layouts.app')

@section('content')
    <h1>Tcodes Details</h1>
    <p>Name: { $tcodes->name }</p>
    <p>Description: { $tcodes->description }</p>
    <a href="{ route('tcodes.edit', $tcodes->id) }">Edit</a> |
    <form action="{ route('tcodes.destroy', $tcodes->id) }" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form> |
    <a href="{ route('tcodes.index') }">Back to List</a>
@endsection