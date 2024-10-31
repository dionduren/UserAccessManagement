@extends('layouts.app')

@section('content')
    <h1>{ ucfirst('composite_roles') } List</h1>
    <ul>
        @foreach ($composite_roles as $item)
            <li>{ $item->name } - <a href="{ route('composite_roles.show', $item->id) }">View</a> | 
            <a href="{ route('composite_roles.edit', $item->id) }">Edit</a> | 
            <form action="{ route('composite_roles.destroy', $item->id) }" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </li>
        @endforeach
    </ul>
    <a href="{ route('composite_roles.create') }">Add New Composite_roles</a>
@endsection