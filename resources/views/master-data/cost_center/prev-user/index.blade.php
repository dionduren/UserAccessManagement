@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Cost Center - Previous User</h1>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {{ session('success') }}
            </div>
        @endif

        <table id="prev_user_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Cost Code</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Dokumen Keterangan</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let masterData = {}; // Store parsed JSON for efficient lookups

            let userGenericTable = $('#prev_user_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('prev-user.index') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'cost_code',
                        name: 'cost_code'
                    },
                    {
                        data: 'user_code',
                        name: 'user_code'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'dokumen_keterangan',
                        name: 'dokumen_keterangan'
                    }
                ],
                responsive: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                columnDefs: [{
                    targets: [0],
                    visible: false
                }],
                order: [
                    [0, 'asc']
                ]
            });

        });
    </script>
@endsection
