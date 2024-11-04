@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Tcode</h1>

        <form action="{{ route('tcodes.update', $tcode) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="company_id" class="form-label">Company</label>
                <select name="company_id" id="company_id" class="form-control">
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ $tcode->company_id == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="code" class="form-label">Tcode Identifier</label>
                <input type="text" class="form-control" name="code" value="{{ $tcode->code }}" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" name="deskripsi">{{ $tcode->deskripsi }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Tcode</button>
        </form>
    </div>
@endsection
