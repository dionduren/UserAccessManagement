@extends('layouts.app')

@section('content')
    <h1>{ ucfirst('companies') } List</h1>
    <ul>
        @foreach ($companies as $item)
            <li>{ $item->name } - <a href="{ route('companies.show', $item->id) }">View</a> | 
            <a href="{ route('companies.edit', $item->id) }">Edit</a> | 
            <form action="{ route('companies.destroy', $item->id) }" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </li>
        @endforeach
    </ul>
    <a href="{ route('companies.create') }">Add New Companies</a>
@endsection