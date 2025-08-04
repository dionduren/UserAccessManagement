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
                        <option value="{{ $group->company_code }}">{{ $group->company_code }} - {{ $group->nama }}</option>
                    @endforeach
                </select>
            </div>


            <div class="mb-3">
                <label>Parent ID</label>
                <input type="text" name="parent_id" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Level</label>
                <select name="level" class="form-control" required>
                    <option value="">Select Level</option>
                    <option value="Direktorat">Direktorat</option>
                    <option value="Kompartemen">Kompartemen</option>
                    <option value="Departemen">Departemen</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Level ID</label>
                <input type="text" name="level_id" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Level Name</label>
                <input type="text" name="level_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Cost Center</label>
                <input type="text" name="cost_center" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Cost Code</label>
                <input type="text" name="cost_code" class="form-control">
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>
@endsection
