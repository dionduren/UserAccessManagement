@extends('layouts.app')

@section('content')
    <h1>{ ucfirst('kompartemen') } List</h1>
    <ul>
        @foreach ($kompartemen as $item)
            <li>{ $item->name } - <a href="{ route('kompartemen.show', $item->id) }">View</a> | 
            <a href="{ route('kompartemen.edit', $item->id) }">Edit</a> | 
            <form action="{ route('kompartemen.destroy', $item->id) }" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </li>
        @endforeach
    </ul>
    <a href="{ route('kompartemen.create') }">Add New Kompartemen</a>
@endsection