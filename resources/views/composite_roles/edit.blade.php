@extends('layouts.app')

@section('content')
    <h1>Edit Composite_roles</h1>
    <form action="{ route('composite_roles.update', $composite_roles->id) }" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="{ $composite_roles->name }" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description">{ $composite_roles->description }</textarea>
        <button type="submit">Update</button>
    </form>
@endsection