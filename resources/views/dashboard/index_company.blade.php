@extends('layouts.app')

@section('content')
    <div>
        @php
            $userCompany = $companyCode ?? (auth()->user()->loginDetail->company_code ?? null);
        @endphp

        <!-- Company Data Table (only user's company) -->
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
                                    @foreach ($data['groupedData']['companies'] as $company)
                                        @if ($company->company_code === $userCompany)
                                            <tr>
                                                <td>1</td>
                                                <td class="text-start">{{ $company->nama }}</td>
                                                <td>{{ $data['groupedData']['data']['kompartemen'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['departemen'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['jobrole'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['compositerole'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['singlerole'][$company->company_code] ?? 0 }}
                                                </td>
                                            </tr>
                                            <tr class="text-center fw-bold " style="background-color: rgb(176, 176, 176)">
                                                <td colspan="2" class="text-end">TOTAL</td>
                                                <td>{{ $data['groupedData']['data']['kompartemen'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['departemen'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['jobrole'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['compositerole'][$company->company_code] ?? 0 }}
                                                </td>
                                                <td>{{ $data['groupedData']['data']['singlerole'][$company->company_code] ?? 0 }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Cards (use per-company values if provided by backend; else fallback) -->
        <div class="row pb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole dengan Job Role</h5>
                        <p class="card-text">
                            {{ $data['compJobPerCompany'][$userCompany] ?? ($data['compJob'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnCompJobEmpty" data-metric="jobRolesComposite" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">CompositeRole tanpa JobRole</h5>
                            <p class="card-text">
                                {{ $data['groupedData']['emptyMetrics']['compJobEmpty'][$userCompany] ?? ($data['compJobEmpty'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">CompositeRole dengan Single Role</h5>
                        <p class="card-text">
                            {{ $data['compSinglePerCompany'][$userCompany] ?? ($data['compSingle'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnCompSingleEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">CompositeRole tanpa Single Role</h5>
                            <p class="card-text">
                                {{ $data['groupedData']['emptyMetrics']['compSingleEmpty'][$userCompany] ?? ($data['compSingleEmpty'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row pb-5">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SingleRole dengan Job Role</h5>
                        <p class="card-text">
                            {{ $data['singleCompPerCompany'][$userCompany] ?? ($data['singleComp'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnSingleCompEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">SingleRole tanpa JobRole</h5>
                            <p class="card-text">
                                {{ $data['groupedData']['emptyMetrics']['singleCompEmpty'][$userCompany] ?? ($data['singleCompEmpty'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SingleRole dengan tCode</h5>
                        <p class="card-text">
                            {{ $data['singleTcodePerCompany'][$userCompany] ?? ($data['singleTcode'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnSingleTcodeEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">SingleRole tanpa tCode</h5>
                            <p class="card-text">
                                {{ $data['groupedData']['emptyMetrics']['singleTcodeEmpty'][$userCompany] ?? ($data['singleTcodeEmpty'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- <div class="row pb-2">
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">tCode dengan Single Role</h5>
                        <p class="card-text">
                            {{ $data['tcodeSingPerCompany'][$userCompany] ?? ($data['tcodeSing'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnTcodeSingEmpty" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">tCode tanpa Single Role</h5>
                            <p class="card-text">
                                {{ $data['groupedData']['emptyMetrics']['tcodeSingEmpty'][$userCompany] ?? ($data['tcodeSingEmpty'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div> --}}

        <hr width="90%" class="my-1 mx-auto">

        <div class="row pt-3 pb-5">
            <div class="col-3"></div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">JobRoles dengan Composite Roles</h5>
                        <p class="card-text">
                            {{ $data['JobCompPerCompany'][$userCompany] ?? ($data['JobComp'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" id="btnJobCompEmpty" data-metric="jobRolesComposite" class="text-decoration-none">
                    <div class="card bg-danger text-white text-center shadow-sm" style="cursor:pointer;">
                        <div class="card-body">
                            <h5 class="card-title">JobRoles tanpa Composite</h5>
                            <p class="card-text fw-bold">
                                {{ $data['groupedData']['emptyMetrics']['JobCompEmpty'][$userCompany] ?? ($data['JobCompEmpty'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Empty Metrics Per Company Table (only user's company) -->
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
                                @if ($company->company_code === $userCompany)
                                    @php $rowTotal = 0; @endphp
                                    <tr>
                                        <td>1</td>
                                        <td class="text-start">{{ $company->nama }}</td>
                                        @foreach ($data['groupedData']['emptyMetrics'] as $metric => $counts)
                                            @php
                                                $val = $counts[$company->company_code] ?? 0;
                                                $rowTotal += $val;
                                            @endphp
                                            <td>{{ $val }}</td>
                                        @endforeach
                                        <td class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">
                                            {{ $rowTotal }}
                                        </td>
                                    </tr>
                                    <tr class="text-center fw-bold" style="background-color: rgb(176, 176, 176)">
                                        <td colspan="2" class="text-end">TOTAL</td>
                                        @foreach ($data['groupedData']['emptyMetrics'] as $metric => $counts)
                                            <td>{{ $counts[$company->company_code] ?? 0 }}</td>
                                        @endforeach
                                        <td>{{ $rowTotal }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal (kept) -->
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
                        <tbody><!-- DataTables will fill this --></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const companyCode = @json($userCompany);

        function showEmptyMetricModal(url, title, isGlobal = false) {
            $('#emptyMetricModalLabel').text(title);
            $('#emptyMetricModal').modal('show');

            if ($.fn.DataTable.isDataTable('#emptyMetricTable')) {
                $('#emptyMetricTable').DataTable().destroy();
                $('#emptyMetricTable tbody').empty();
            }

            // Decide final URL (no company filter for global SingleRole / Tcode endpoints)
            let finalUrl = url;
            if (!isGlobal) {
                const sep = url.includes('?') ? '&' : '?';
                finalUrl = url + sep + 'company_code=' + encodeURIComponent(companyCode);
            }

            // Build columns depending on scope
            let columns = [{
                    data: null,
                    render: (d, t, r, m) => m.row + 1
                },
                {
                    data: 'nama'
                }
            ];

            if (isGlobal) {
                // For global endpoints (SingleRole / Tcode) there is no company column returned.
                // Show a placeholder '-' so table layout stays consistent.
                columns.push({
                    data: null,
                    render: () => '-'
                });
            } else {
                // Company-scoped endpoints still return company relation
                columns.push({
                    data: 'company.nama',
                    defaultContent: '-'
                });
            }

            $('#emptyMetricTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: finalUrl,
                    dataSrc: ''
                },
                columns: columns
            });
        }

        // Company–scoped (still filtered by company)
        $('#btnJobCompEmpty').on('click', function(e) {
            e.preventDefault();
            showEmptyMetricModal("{{ route('home.empty.jobRolesComposite') }}", "JobRoles tanpa Composite", false);
        });
        $('#btnCompJobEmpty').on('click', function(e) {
            e.preventDefault();
            showEmptyMetricModal("{{ route('home.empty.compositeRolesJob') }}", "CompositeRole tanpa JobRole",
                false);
        });
        $('#btnCompSingleEmpty').on('click', function(e) {
            e.preventDefault();
            showEmptyMetricModal("{{ route('home.empty.compositeRolesSingle') }}",
                "CompositeRole tanpa Single Role", false);
        });

        // GLOBAL (SingleRole & Tcode no longer tied to company)
        $('#btnSingleCompEmpty').on('click', function(e) {
            e.preventDefault();
            showEmptyMetricModal("{{ route('home.empty.singleRolesComposite') }}", "SingleRole tanpa JobRole",
                true);
        });
        $('#btnSingleTcodeEmpty').on('click', function(e) {
            e.preventDefault();
            showEmptyMetricModal("{{ route('home.empty.singleRolesTcode') }}", "SingleRole tanpa tCode", true);
        });
        $('#btnTcodeSingEmpty').on('click', function(e) {
            e.preventDefault();
            showEmptyMetricModal("{{ route('home.empty.tcodesSingle') }}", "tCode tanpa Single Role", true);
        });
    </script>
@endsection
