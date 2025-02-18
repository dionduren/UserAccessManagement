@extends('layouts.app')

@section('content')
    <!-- Error Message -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <h4>Error(s) occurred:</h4>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container">
        <h2>Create Cost Center</h2>
        <form action="{{ route('cost-center.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Group</label>
                <select name="group" class="form-control">
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($shortName as $group)
                        <option value="{{ $group->shortname }}">{{ $group->shortname }} - {{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Cost Center</label>
                <input type="text" name="cost_center" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Cost Code</label>
                <input type="text" name="cost_code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>
@endsection
