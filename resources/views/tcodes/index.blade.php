@extends('layouts.app')

@section('content')
    <h1>{ ucfirst('tcodes') } List</h1>
    <ul>
        @foreach ($tcodes as $item)
            <li>{ $item->name } - <a href="{ route('tcodes.show', $item->id) }">View</a> | 
            <a href="{ route('tcodes.edit', $item->id) }">Edit</a> | 
            <form action="{ route('tcodes.destroy', $item->id) }" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </li>
        @endforeach
    </ul>
    <a href="{ route('tcodes.create') }">Add New Tcodes</a>
@endsection