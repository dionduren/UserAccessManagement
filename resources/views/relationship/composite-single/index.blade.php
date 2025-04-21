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
                        <h3 class="card-title">List Composite Role - Single Role</h3>
                        <div class="card-tools">
                            <a href="{{ route('composite-single.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Create
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="composite-single-datatable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Composite Role</th>
                                    <th>Single Role</th>
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
            $('#composite-single-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('composite-single.jsonIndex') }}',
                columns: [{
                        data: 'nama',
                        name: 'Composite Role'
                    },
                    {
                        data: 'singleRoles',
                        name: 'Single Role'
                    },
                    {
                        data: 'action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    },
                ],
                columnDefs: [{
                    targets: 2,
                    render: function(data, type, row) {
                        return `
                            <a href="${data.edit_url}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteData('${data.delete_url}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        `;
                    }
                }]
            });
        });

        function deleteData(url) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('<form>', {
                        method: 'POST',
                        action: url
                    });

                    const token = $('meta[name="csrf-token"]').attr('content');

                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: token
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_method',
                        value: 'DELETE'
                    }));

                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
