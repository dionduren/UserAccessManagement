@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- General Error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h1>Ubah Master Data Kompartemen</h1>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('kompartemens.update', $kompartemen->kompartemen_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Company Dropdown -->
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Company</label>
                        <select name="company_id" class="form-control" required>
                            <option value="">Select a company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}"
                                    {{ $company->company_code == $kompartemen->company_id ? 'selected' : '' }}>
                                    {{ $company->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kompartemen ID -->
                    <div class="mb-3">
                        <label for="kompartemen_id" class="form-label">Kode Kompartemen</label>
                        <input type="text" class="form-control" name="kompartemen_id"
                            value="{{ $kompartemen->kompartemen_id }}" required>
                    </div>

                    <!-- Kompartemen Name -->
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kompartemen</label>
                        <input type="text" class="form-control" name="nama" value="{{ $kompartemen->nama }}" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi">{{ $kompartemen->deskripsi }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Kompartemen</button>
                </form>
            </div>
        </div>
    </div>
@endsection
