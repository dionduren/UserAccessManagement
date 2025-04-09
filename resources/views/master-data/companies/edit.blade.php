@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Master Data Perusahaan</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('companies.update', $company) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="company_code" class="form-label">Company Code</label>
                <input type="text" class="form-control" name="company_code" value="{{ $company->company_code }}" required>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Company Name</label>
                <input type="text" class="form-control" name="nama" value="{{ $company->nama }}" required>
            </div>

            <div class="mb-3">
                <label for="shortname" class="form-label">Singkatan</label>
                <input type="text" class="form-control" name="shortname" value="{{ $company->shortname }}">
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi">{{ $company->deskripsi }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Company</button>
        </form>
    </div>
@endsection
