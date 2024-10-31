@extends('layouts.app')

@section('content')
    <h1>{ ucfirst('job_roles') } List</h1>
    <ul>
        @foreach ($job_roles as $item)
            <li>{ $item->name } - <a href="{ route('job_roles.show', $item->id) }">View</a> | 
            <a href="{ route('job_roles.edit', $item->id) }">Edit</a> | 
            <form action="{ route('job_roles.destroy', $item->id) }" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </li>
        @endforeach
    </ul>
    <a href="{ route('job_roles.create') }">Add New Job_roles</a>
@endsection