@extends('layouts.app')

@section('content')
    <h1>Edit Kompartemen</h1>
    <form action="{ route('kompartemen.update', $kompartemen->id) }" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="{ $kompartemen->name }" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description">{ $kompartemen->description }</textarea>
        <button type="submit">Update</button>
    </form>
@endsection