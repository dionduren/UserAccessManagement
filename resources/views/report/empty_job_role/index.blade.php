@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Report Empty Job Role</h1>

        <div class="card">
            <div class="card-header">
                List Job Role
            </div>
            <div class="card-body">
                <div id="jobRole-table"></div>
            </div>
        </div>
    </div>

    @php
        $formattedRoles = $jobRoles->map(function ($jobRole) {
            return [
                'company' => $jobRole->company->nama ?? '-',
                'kompartemen' => $jobRole->kompartemen->nama ?? '-',
                'departemen' => $jobRole->departemen->nama ?? '-',
                'nama' => $jobRole->nama,
            ];
        });
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            var jobRoles = @json($formattedRoles);

            var table = new Tabulator("#jobRole-table", {
                layout: "fitColumns",
                responsiveLayout: "collapse",
                pagination: "local",
                paginationSize: 10,
                paginationSizeSelector: [10, 20, 30, 40, 50],
                columns: [{
                        title: "Company",
                        field: "company",
                        sorter: "string",
                        headerFilter: "input"
                    },
                    {
                        title: "Kompartemen",
                        field: "kompartemen",
                        sorter: "string",
                        headerFilter: "input"
                    },
                    {
                        title: "Departemen",
                        field: "departemen",
                        sorter: "string",
                        headerFilter: "input"
                    },
                    {
                        title: "Nama Jabatan",
                        field: "nama",
                        sorter: "string",
                        headerFilter: "input"
                    },
                ],

                data: jobRoles,
            });
        });
    </script>
@endsection
