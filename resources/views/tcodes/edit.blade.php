@extends('layouts.app')

@section('content')
    <h1>Edit Tcodes</h1>
    <form action="{ route('tcodes.update', $tcodes->id) }" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="{ $tcodes->name }" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description">{ $tcodes->description }</textarea>
        <button type="submit">Update</button>
    </form>
@endsection