@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Terminated Employees</h1>

        <a href="{{ route('terminated-employee.create') }}" class="btn btn-primary mb-3">+ Add</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div id="terminated-employees-table" class="mb-3"></div>

        <script>
            $(function() {
                var table = new Tabulator("#terminated-employees-table", {
                    ajaxURL: "{{ route('terminated-employee.get-data') }}",
                    layout: "fitColumns",
                    pagination: "remote",
                    paginationSize: 10,
                    ajaxResponse: function(url, params, response) {
                        return response.data; // 👈 tell Tabulator where the array lives
                    },
                    columns: [{
                            title: "NIK",
                            field: "nik",
                            headerFilter: true
                        },
                        {
                            title: "Nama",
                            field: "nama",
                            headerFilter: true
                        },
                        {
                            title: "Tanggal Resign",
                            field: "tanggal_resign",
                            headerFilter: true
                        },
                        {
                            title: "Status",
                            field: "status",
                            headerFilter: true
                        },
                        {
                            title: "Last Login",
                            field: "last_login",
                            headerFilter: true
                        },
                        {
                            title: "Valid From",
                            field: "valid_from",
                            headerFilter: true
                        },
                        {
                            title: "Valid To",
                            field: "valid_to",
                            headerFilter: true
                        },
                        {
                            title: "Action",
                            field: "action",
                            formatter: function(cell, formatterParams) {
                                const data = cell.getData();
                                const editUrl = `/terminated-employee/${data.id}/edit`;
                                const deleteUrl = `/terminated-employee/${data.id}`;

                                return `
            <a href="${editUrl}" class="btn btn-sm btn-warning">Edit</a>
            <form action="${deleteUrl}" method="POST" class="d-inline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" onclick="return confirm('Sure?')" class="btn btn-sm btn-danger">🗑️</button>
            </form>
        `;
                            }
                        }
                    ]
                });
            });
        </script>
    </div>
@endsection
