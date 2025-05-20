@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Report Filled Job Role</h1>

        <div class="card">
            <div class="card-header">
                List Job Role with Assigned Users
            </div>
            <div class="card-body">
                <div id="jobRole-table"></div>
            </div>
        </div>
    </div>

    @php
        $formattedRoles = $jobRoles->map(function ($jobRole) {
            // Get all users assigned to this role
            $users = $jobRole->NIKJobRole->map(function ($nikJobRole) {
                return [
                    'nik' => $nikJobRole->userDetail->nik ?? '-',
                    'nama' => $nikJobRole->userDetail->nama ?? '-',
                ];
            });

            return [
                'company' => $jobRole->company->nama ?? '-',
                'kompartemen' => $jobRole->kompartemen->nama ?? '-',
                'departemen' => $jobRole->departemen->nama ?? '-',
                'nama_jabatan' => $jobRole->nama,
                'users' => $users,
                'assigned_users' => $users->pluck('nama')->join(', '),
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
                        field: "nama_jabatan",
                        sorter: "string",
                        headerFilter: "input"
                    },
                    {
                        title: "Assigned Users",
                        field: "users",
                        headerFilter: "input",
                        headerFilterFunc: "like",
                        formatter: function(cell) {
                            var users = cell.getValue();
                            var html = users.map(function(user) {
                                return `<div class="user-assignment mb-1">
            <strong>${user.nama}</strong> (${user.nik})
        </div>`;
                            }).join('');
                            return html;
                        },
                        // For filtering
                        accessorData: "assigned_users"
                    }
                ],
                data: jobRoles,
            });
        });
    </script>

    <style>
        .user-assignment {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        .user-assignment:last-child {
            border-bottom: none;
        }
    </style>
@endsection
