@extends('layouts.app')

@section('content')
    <h1>Edit Departemen</h1>
    <form action="{ route('departemen.update', $departemen->id) }" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="{ $departemen->name }" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description">{ $departemen->description }</textarea>
        <button type="submit">Update</button>
    </form>
@endsection