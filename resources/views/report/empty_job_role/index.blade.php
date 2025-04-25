@extends('layouts.app')

@section('header-scripts')
    <!-- Tabulator CSS -->
    <link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/6.3.0/css/tabulator_semanticui.min.css" rel="stylesheet">
    <!-- Tabulator JS -->
    <script src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>
@endsection

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
