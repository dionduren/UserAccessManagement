@extends('layouts.app')

@section('content')
    <h1>Create New Tcodes</h1>
    <form action="{ route('tcodes.store') }" method="POST">
        @csrf
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description"></textarea>
        <button type="submit">Save</button>
    </form>
@endsection