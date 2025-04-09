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
                                        <th>Single Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['company'] as $company)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-start">{{ $company->nama }}</td>
                                            <td>{{ $data['groupedData']['kompartemen']->where('company_id', $company->company_code)->sum('total') }}
                                            </td>
                                            <td>{{ $data['groupedData']['departemen']->where('company_id', $company->company_code)->sum('total') }}
                                            </td>
                                            <td>{{ $data['groupedData']['jobrole']->where('company_id', $company->company_code)->sum('total') }}
                                            </td>
                                            <td>{{ $data['groupedData']['compositerole']->where('company_id', $company->company_code)->sum('total') }}
                                            </td>
                                            <td>{{ $data['groupedData']['singlerole']->where('company_id', $company->company_code)->sum('total') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="text-start fw-bold" style="background-color: rgb(176, 176, 176)">
                                        <td colspan="2" class="text-end">TOTAL
                                        </td>
                                        <td>{{ $data['kompartemen'] }}</td>
                                        <td>{{ $data['departemen'] }}</td>
                                        <td>{{ $data['jobRole'] }}</td>
                                        <td>{{ $data['compositeRole'] }}</td>
                                        <td>{{ $data['singleRole'] }}</td>
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
                        <h5 class="card-title">JobRoles dengan Composite Roles</h5>
                        <p class="card-text">{{ $data['JobComp'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">JobRoles tanpa Composite</h5>
                        <p class="card-text">{{ $data['JobCompEmpty'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row pb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole dengan Job Role</h5>
                        <p class="card-text">{{ $data['compJob'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole tanpa JobRole</h5>
                        <p class="card-text">{{ $data['compJobEmpty'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole dengan Single Role</h5>
                        <p class="card-text">{{ $data['compSingle'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole tanpa Single Role</h5>
                        <p class="card-text">{{ $data['compSingleEmpty'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row pb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SingleRole dengan Job Role</h5>
                        <p class="card-text">{{ $data['singleComp'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SingleRole tanpa JobRole</h5>
                        <p class="card-text">{{ $data['singleCompEmpty'] }}</p>
                    </div>
                </div>
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
                <div class="card bg-danger text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SingleRole tanpa tCode</h5>
                        <p class="card-text">{{ $data['singleTcodeEmpty'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row pb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">tCode dengan Single Role</h5>
                        <p class="card-text">{{ $data['tcodeSing'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">tCode tanpa Single Role</h5>
                        <p class="card-text">{{ $data['tcodeSingEmpty'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            @foreach ($data['groupedData'] as $key => $dataset)
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chartData = @json($data['groupedData']);
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

        Object.keys(chartData).forEach(function(key) {
            let ctx = document.getElementById('chart_' + key).getContext('2d');
            let sortedData = chartData[key].sort((a, b) => a.company_id - b.company_id);
            let myData = sortedData.map(item => item.total);
            let myLabels = sortedData.map(item => item.company ? item.company.name : 'Unknown');
            let myColors = sortedData.map(item => {
                let companyName = item.company ? item.company.name : 'Unknown';
                let colorStops = companyColorMap[companyName] || ['#000000'];
                return colorStops;
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: myLabels,
                    datasets: [{
                        label: key,
                        data: myData,
                        backgroundColor: myColors,
                        borderColor: 'black',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    // maintainAspectRatio: true,
                    animation: {
                        duration: 0, // Disable animation so gradients compute immediately
                        onComplete: function() {
                            let chartInstance = this;
                            let ctx = chartInstance.ctx;
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.fillStyle = 'black';

                            chartInstance.data.datasets.forEach((dataset, i) => {
                                let meta = chartInstance.getDatasetMeta(i);
                                meta.data.forEach((bar, index) => {
                                    let data = dataset.data[index];
                                    ctx.fillText(data, bar.x, bar.y - 5);
                                });
                            });
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: key + ' Data'
                        },
                        tooltip: {
                            enabled: true
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
    </script>
@endsection
