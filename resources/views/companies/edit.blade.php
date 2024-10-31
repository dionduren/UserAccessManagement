@extends('layouts.app')

@section('content')
    <h1>Edit Companies</h1>
    <form action="{ route('companies.update', $companies->id) }" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="{ $companies->name }" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description">{ $companies->description }</textarea>
        <button type="submit">Update</button>
    </form>
@endsection