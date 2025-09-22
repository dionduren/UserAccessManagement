@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Master Data Periode</h1>

        @if ($user_company === 'A000')
            <a href="{{ route('periode.create') }}" target="_blank" class="btn btn-outline-primary mb-3">
                <i class="bi bi-plus"></i> Buat Periode Baru
            </a>
        @endif

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <h4>Success:</h4>
                {!! session('success') !!}
            </div>
        @endif

        <table id="periode_table" class="table table-bordered table-striped table-hover cell-border mt-3">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Definisi</th>
                    <th>Tanggal Pembuatan</th>
                    @if ($user_company === 'A000')
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const showAction = @json($user_company === 'A000');

            let columns = [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'definisi',
                    name: 'definisi'
                },
                {
                    data: 'tanggal_create_periode',
                    name: 'tanggal_create_periode'
                },
            ];

            if (showAction) {
                columns.push({
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                });
            }

            $('#periode_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('periode.index') }}",
                columns: columns,
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
                    [2, 'asc']
                ]
            });
        });
    </script>
@endsection
