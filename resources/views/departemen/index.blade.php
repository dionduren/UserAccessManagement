@extends('layouts.app')

@section('content')
    <h1>{ ucfirst('departemen') } List</h1>
    <ul>
        @foreach ($departemen as $item)
            <li>{ $item->name } - <a href="{ route('departemen.show', $item->id) }">View</a> | 
            <a href="{ route('departemen.edit', $item->id) }">Edit</a> | 
            <form action="{ route('departemen.destroy', $item->id) }" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </li>
        @endforeach
    </ul>
    <a href="{ route('departemen.create') }">Add New Departemen</a>
@endsection