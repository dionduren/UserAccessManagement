@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Edit Previous User</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('prev-user.full-update', $costPrevUser->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label for="user_code" class="col-md-4 col-form-label text-md-right">User Code</label>

                                <div class="col-md-6">
                                    <input id="user_code" type="text"
                                        class="form-control @error('user_code') is-invalid @enderror" name="user_code"
                                        value="{{ $costPrevUser->user_code }}" required autocomplete="user_code" autofocus>

                                    @error('user_code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="user_name" class="col-md-4 col-form-label text-md-right">User Name</label>

                                <div class="col-md-6">
                                    <input id="user_name" type="text"
                                        class="form-control @error('user_name') is-invalid @enderror" name="user_name"
                                        value="{{ $costPrevUser->user_name }}" required autocomplete="user_name">

                                    @error('user_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="cost_code" class="col-md-4 col-form-label text-md-right">Cost Code</label>

                                <div class="col-md-6">
                                    <input id="cost_code" type="text"
                                        class="form-control @error('cost_code') is-invalid @enderror" name="cost_code"
                                        value="{{ $costPrevUser->cost_code }}" required autocomplete="cost_code">

                                    @error('cost_code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="flagged" class="col-md-4 col-form-label text-md-right">Flagged</label>

                                <div class="col-md-6">
                                    <select id="flagged" class="form-control @error('flagged') is-invalid @enderror"
                                        name="flagged" required>
                                        <option value="0" {{ $costPrevUser->flagged == 0 ? 'selected' : '' }}>No
                                        </option>
                                        <option value="1" {{ $costPrevUser->flagged == 1 ? 'selected' : '' }}>Yes
                                        </option>
                                    </select>

                                    @error('flagged')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-right">Keterangan</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        rows="3">{{ $costPrevUser->keterangan }}</textarea>

                                    @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
