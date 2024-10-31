@extends('layouts.app')

@section('content')
    <h1>Edit Single_roles</h1>
    <form action="{ route('single_roles.update', $single_roles->id) }" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="{ $single_roles->name }" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description">{ $single_roles->description }</textarea>
        <button type="submit">Update</button>
    </form>
@endsection