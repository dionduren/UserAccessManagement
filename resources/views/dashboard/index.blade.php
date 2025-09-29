@extends('layouts.app')

@section('content')
    <div>

        <!-- Company Data Table -->
        <div class="row pb-3">
            <div class="col-12">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5>Company Data</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-responsive">
                                <thead class="thead-dark" style="vertical-align: middle">
                                    <tr>
                                        <th>No.</th>
                                        <th>Company</th>
                                        <th>Kompartemen</th>
                                        <th>Departemen</th>
                                        <th>Job Role</th>
                                        <th>Composite Role</th>
                                        <th>Single Role (Global)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['groupedData']['companies'] as $company)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-start">{{ $company->nama }}</td>
                                            <td>{{ $data['groupedData']['data']['kompartemen'][$company->company_code] ?? 0 }}
                                            </td>
                                            <td>{{ $data['groupedData']['data']['departemen'][$company->company_code] ?? 0 }}
                                            </td>
                                            <td>{{ $data['groupedData']['data']['jobrole'][$company->company_code] ?? 0 }}
                                            </td>
                                            <td>{{ $data['groupedData']['data']['compositerole'][$company->company_code] ?? 0 }}
                                            </td>
                                            <td>-</td> {{-- Single Role is now global (no company split) --}}
                                        </tr>
                                    @endforeach
                                    <tr class="text-center fw-bold " style="background-color: rgb(176, 176, 176)">
                                        <td colspan="2" class="text-end">TOTAL</td>
                                        <td>{{ $data['kompartemen'] }}</td>
                                        <td>{{ $data['departemen'] }}</td>
                                        <td>{{ $data['jobRole'] }}</td>
                                        <td>{{ $data['compositeRole'] }}</td>
                                        <td>{{ $data['singleRole'] }}</td> {{-- Global total --}}
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Data Table -->
        <div class="row pb-5">
            <div class="col-12">

                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5>tCode Count - TOTAL = {{ $data['tcode'] }}</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>Modul SAP</th>
                                        <th>Jumlah tCount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>FI</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>CO</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>SD</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>MM</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>PP</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>6</td>
                                        <td>QM</td>
                                        <td>0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row pb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole dengan Single Role</h5>
                        <p class="card-text">{{ $data['compSingle'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnCompSingleEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">CompositeRole tanpa Single Role</h5>
                            <p class="card-text">{{ $data['compSingleEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SingleRole dengan Composite Role</h5>
                        <p class="card-text">{{ $data['singleComp'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnSingleCompEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">SingleRole tanpa Composite Role</h5>
                            <p class="card-text">{{ $data['singleCompEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SingleRole dengan tCode</h5>
                        <p class="card-text">{{ $data['singleTcode'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnSingleTcodeEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">SingleRole tanpa tCode</h5>
                            <p class="card-text">{{ $data['singleTcodeEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row pb-3">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">tCode dengan Single Role</h5>
                        <p class="card-text">{{ $data['tcodeSing'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnTcodeSingEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">tCode tanpa Single Role</h5>
                            <p class="card-text">{{ $data['tcodeSingEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- User ID - Job Role Metrics Cards -->

        <hr width="90%" class="my-2 mx-auto">

        <div class="row pt-2 pb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">JobRoles dengan Composite Roles</h5>
                        <p class="card-text">{{ $data['JobComp'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnJobCompEmpty" data-metric="jobRolesComposite" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">JobRoles tanpa Composite</h5>
                            <p class="card-text fw-bold">{{ $data['JobCompEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole dengan Job Role</h5>
                        <p class="card-text">{{ $data['compJob'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnCompJobEmpty" data-metric="compositeRolesJob" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">CompositeRole tanpa JobRole</h5>
                            <p class="card-text">{{ $data['compJobEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">User NIK dengan JobRole</h5>
                        <p class="card-text">{{ $data['nikJob'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnNikJobEmpty" data-metric="nikJobEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">User NIK tanpa JobRole</h5>
                            <p class="card-text">{{ $data['nikJobEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">User Generic dengan JobRole</h5>
                        <p class="card-text">{{ $data['genericJob'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnGenericJobEmpty" data-metric="genericJobEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">User Generic tanpa JobRole</h5>
                            <p class="card-text">{{ $data['genericJobEmpty'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Empty Metrics Per Company Table -->

        <div class="card text-center shadow-sm mb-5">
            <div class="card-body">
                <h5>Data UAM tanpa Relationship</h5>
                <div class="table-responsive mt-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Company</th>
                                @foreach ($data['groupedData']['emptyMetrics'] as $metric => $counts)
                                    <th>
                                        @switch($metric)
                                            @case('JobCompEmpty')
                                                JobRoles tanpa Composite
                                            @break

                                            @case('compJobEmpty')
                                                CompositeRole tanpa JobRole
                                            @break

                                            @case('compSingleEmpty')
                                                CompositeRole tanpa Single Role
                                            @break

                                            @case('singleCompEmpty')
                                                SingleRole tanpa JobRole
                                            @break

                                            @case('singleTcodeEmpty')
                                                SingleRole tanpa tCode
                                            @break

                                            @case('tcodeSingEmpty')
                                                tCode tanpa Single Role
                                            @break

                                            @case('nikJobEmpty')
                                                User NIK tanpa JobRole
                                            @break

                                            @case('genericJobEmpty')
                                                User Generic tanpa JobRole
                                            @break

                                            @default
                                                {{ $metric }}
                                        @endswitch
                                    </th>
                                @endforeach
                                <th class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['groupedData']['companies'] as $company)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-start">{{ $company->nama }}</td>
                                    @php
                                        $rowTotal = 0;
                                    @endphp
                                    @foreach ($data['groupedData']['emptyMetrics'] as $metric => $counts)
                                        @php
                                            $val = $counts[$company->company_code] ?? 0;
                                            $rowTotal += $val;
                                        @endphp
                                        <td>{{ $val }}</td>
                                    @endforeach
                                    <td class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">
                                        {{ $rowTotal }}</td>
                                </tr>
                            @endforeach
                            {{-- Total row --}}
                            <tr class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">
                                <td colspan="2" class="text-end">TOTAL</td>
                                @php
                                    $colTotals = [];
                                    $grandTotal = 0;
                                    foreach ($data['groupedData']['emptyMetrics'] as $metric => $counts) {
                                        $colTotals[$metric] = 0;
                                        foreach ($data['groupedData']['companies'] as $company) {
                                            $colTotals[$metric] += $counts[$company->company_code] ?? 0;
                                        }
                                        $grandTotal += $colTotals[$metric];
                                    }
                                @endphp
                                @foreach ($data['groupedData']['emptyMetrics'] as $metric => $counts)
                                    <td>{{ $colTotals[$metric] }}</td>
                                @endforeach
                                <td>{{ $grandTotal }}</td>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- UAR TABLE HERE --}}
        @php
            $uarMetrics = $data['groupedDataUAR'] ?? [];
        @endphp
        @if (!empty($uarMetrics))
            <div class="card text-center shadow-sm mb-5">
                <div class="card-body">
                    <h5>Data UAR per Company</h5>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Company</th>
                                    @foreach ($uarMetrics as $metric => $counts)
                                        <th>
                                            @switch($metric)
                                                @case('nikJob')
                                                    User NIK dengan JobRole
                                                @break

                                                @case('nikJobEmpty')
                                                    User NIK tanpa JobRole
                                                @break

                                                @case('genericJob')
                                                    User Generic dengan JobRole
                                                @break

                                                @case('genericJobEmpty')
                                                    User Generic tanpa JobRole
                                                @break

                                                @default
                                                    {{ \Illuminate\Support\Str::headline($metric) }}
                                            @endswitch
                                        </th>
                                    @endforeach
                                    <th class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['company'] as $company)
                                    @php
                                        $rowTotal = 0;
                                        $shortname = $company->shortname ?? $company->company_code;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="text-start">{{ $company->nama }}</td>
                                        @foreach ($uarMetrics as $counts)
                                            @php
                                                $value = $counts[$shortname] ?? 0;
                                                $rowTotal += $value;
                                            @endphp
                                            <td>{{ $value }}</td>
                                        @endforeach
                                        <td class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">
                                            {{ $rowTotal }}</td>
                                    </tr>
                                @endforeach
                                <tr class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">
                                    <td colspan="2" class="text-end">TOTAL</td>
                                    @php
                                        $metricTotals = [];
                                        $grandTotal = 0;
                                        foreach ($uarMetrics as $metric => $counts) {
                                            $metricTotals[$metric] = 0;
                                            foreach ($data['company'] as $company) {
                                                $shortname = $company->shortname ?? $company->company_code;
                                                $metricTotals[$metric] += $counts[$shortname] ?? 0;
                                            }
                                            $grandTotal += $metricTotals[$metric];
                                        }
                                    @endphp
                                    @foreach ($uarMetrics as $metric => $counts)
                                        <td>{{ $metricTotals[$metric] }}</td>
                                    @endforeach
                                    <td>{{ $grandTotal }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Charts -->
        <div class="row">
            @foreach ($data['groupedData']['data'] as $key => $counts)
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5>{{ ucfirst($key) }} Data</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chart_{{ $key }}"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bootstrap Modal for Details -->
    <div class="modal fade" id="emptyMetricModal" tabindex="-1" aria-labelledby="emptyMetricModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emptyMetricModalLabel">Detail Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="emptyMetricTable" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Company</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will fill this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let companies = @json($data['groupedData']['companies']);
        let groupedData = @json($data['groupedData']['data']); // no singlerole distribution now

        // Remove any accidental singlerole dataset (global now)
        if (groupedData.singlerole) {
            delete groupedData.singlerole;
        }

        let companyColorMap = {
            'PT Pupuk Indonesia (Persero)': 'LavenderBlush',
            'PT Petrokimia Gresik': 'YellowGreen',
            'PT Pupuk Kujang Cikampek': '#FAFF00',
            'PT Pupuk Kalimantan Timur': '#F47920',
            'PT Pupuk Iskandar Muda': '#0800FF',
            'PT Pupuk Sriwidjaja Palembang': 'PaleGreen',
            'PT Rekayasa Industri': 'LightCoral',
            'PT Pupuk Indonesia Niaga': 'MediumSpringGreen',
            'PT Pupuk Indonesia Logistik': '#00AEFF',
            'PT Pupuk Indonesia Utilitas': 'LightSkyBlue',
            'PT Kaltim Daya Mandiri': 'Turquoise',
            'PT Pupuk Indonesia Pangan': 'Indigo'
        };

        Object.keys(groupedData).forEach(function(key) {
            let ctx = document.getElementById('chart_' + key)?.getContext('2d');
            if (!ctx) return;
            let labels = companies.map(c => c.nama);
            let values = companies.map(c => groupedData[key][c.company_code] ?? 0);
            let colors = companies.map(c => companyColorMap[c.nama] || '#4e73df');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: key,
                        data: values,
                        backgroundColor: colors,
                        borderColor: 'black',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 0
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: key + ' Data'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        $(document).ready(function() {
            function showEmptyMetricModal(url, title, isGlobal = false) {
                $('#emptyMetricModalLabel').text(title);
                $('#emptyMetricModal').modal('show');

                if ($.fn.DataTable.isDataTable('#emptyMetricTable')) {
                    $('#emptyMetricTable').DataTable().destroy();
                    $('#emptyMetricTable tbody').empty();
                }

                let columns = [{
                        data: null,
                        render: (d, t, r, m) => m.row + 1
                    },
                    {
                        data: 'nama'
                    }
                ];

                if (isGlobal) {
                    // Add placeholder company column
                    columns.push({
                        data: null,
                        render: () => '-',
                        defaultContent: '-'
                    });
                } else {
                    columns.push({
                        data: 'company.nama',
                        defaultContent: '-'
                    });
                }

                $('#emptyMetricTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: url,
                        dataSrc: ''
                    },
                    columns: columns
                });
            }

            // Company-scoped
            $('#btnJobCompEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.jobRolesComposite') }}",
                    "JobRoles tanpa Composite", false);
            });
            $('#btnCompJobEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.compositeRolesJob') }}",
                    "CompositeRole tanpa JobRole", false);
            });
            $('#btnCompSingleEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.compositeRolesSingle') }}",
                    "CompositeRole tanpa Single Role", false);
            });

            // Global (no company relation)
            $('#btnSingleCompEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.singleRolesComposite') }}",
                    "SingleRole tanpa JobRole", true);
            });
            $('#btnSingleTcodeEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.singleRolesTcode') }}",
                    "SingleRole tanpa tCode", true);
            });
            $('#btnTcodeSingEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.tcodesSingle') }}", "tCode tanpa Single Role",
                    true);
            });
            $('#btnNikJobEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.nikJob') }}", "User NIK tanpa JobRole",
                    false);
            });
            $('#btnGenericJobEmpty').on('click', e => {
                e.preventDefault();
                showEmptyMetricModal("{{ route('home.empty.genericJob') }}", "User Generic tanpa JobRole",
                    true);
            });
        });
    </script>
@endsection
