@extends('layouts.app')

@section('content')
    <h1>{ ucfirst('single_roles') } List</h1>
    <ul>
        @foreach ($single_roles as $item)
            <li>{ $item->name } - <a href="{ route('single_roles.show', $item->id) }">View</a> | 
            <a href="{ route('single_roles.edit', $item->id) }">Edit</a> | 
            <form action="{ route('single_roles.destroy', $item->id) }" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </li>
        @endforeach
    </ul>
    <a href="{ route('single_roles.create') }">Add New Single_roles</a>
@endsection