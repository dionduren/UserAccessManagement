@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Email Change Requests</h4>
            </div>
            <div class="card-body">
                <table id="email-change-requests" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Current</th>
                            <th>New</th>
                            <th>Status</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#email-change-requests').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.email-change-requests.data') }}',
                columns: [{
                        data: 'user_name',
                        name: 'user.name'
                    },
                    {
                        data: 'current_email',
                        name: 'current_email'
                    },
                    {
                        data: 'new_email',
                        name: 'new_email'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'asc']
                ]
            });
        });
    </script>
@endsection
