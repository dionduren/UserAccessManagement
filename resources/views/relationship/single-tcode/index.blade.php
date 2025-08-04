@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Success Message -->
                @if (session('success'))
                    <div class="alert alert-success">
                        <h4>Success:</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages -->
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
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">List Single Role - Tcode</h3>
                        <div class="card-tools">
                            <a href="{{ route('single-tcode.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Create
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="single-tcode-datatable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Single Role</th>
                                    <th>Tcode</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#single-tcode-datatable').DataTable({
                processing: true,
                serverSide: false,
                ajax: '{{ route('single-tcode.jsonIndex') }}',
                columns: [{
                        data: 'single_role_name',
                        name: 'single_role_name'
                    },
                    {
                        data: 'tcodes',
                        name: 'tcodes'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

        });
    </script>
@endsection
