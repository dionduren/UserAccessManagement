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
        <h2>Edit Cost Center</h2>
        <form action="{{ route('cost-center.update', $costCenter->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label>Group</label>
                <select name="group" class="form-control">
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($shortName as $group)
                        <option value="{{ $group->shortname }}"
                            {{ $group->shortname == $costCenter->group ? 'selected' : '' }}>{{ $group->shortname }} -
                            {{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Cost Center</label>
                <input type="text" name="cost_center" value="{{ $costCenter->cost_center }}" class="form-control"
                    required>
            </div>
            <div class="mb-3">
                <label>Cost Code</label>
                <input type="text" name="cost_code" value="{{ $costCenter->cost_code }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="deskripsi" class="form-control">{{ $costCenter->deskripsi }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
@endsection
