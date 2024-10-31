@extends('layouts.app')

@section('content')
    <h1>Edit Job_roles</h1>
    <form action="{ route('job_roles.update', $job_roles->id) }" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="{ $job_roles->name }" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description">{ $job_roles->description }</textarea>
        <button type="submit">Update</button>
    </form>
@endsection