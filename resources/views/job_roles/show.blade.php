@extends('layouts.app')

@section('content')
    <h1>Job_roles Details</h1>
    <p>Name: { $job_roles->name }</p>
    <p>Description: { $job_roles->description }</p>
    <a href="{ route('job_roles.edit', $job_roles->id) }">Edit</a> |
    <form action="{ route('job_roles.destroy', $job_roles->id) }" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form> |
    <a href="{ route('job_roles.index') }">Back to List</a>
@endsection