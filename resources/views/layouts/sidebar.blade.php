<div class="sidebar-heading mt-2">
    <div class="row">
        <span class="fs-4">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <img src="{{ asset('images/logo-perusahaan/icon-a000-transparent.png') }}" alt="Company Logo"
                    style="height: 48px;"> <b>UAM & UAR TOOLS</b>
            </a>
        </span>
    </div>
</div>
<hr>
<div class="sidebar-scroll">
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : 'text-white' }}">
                <i class="bi bi-house-door me-2"></i> Home
            </a>
        </li>


        <!-- Other Sidebar Links -->
        @can('data.view')
            {{-- <div class="mx-1">
                <li>
                    <a href="{{ route('cost-center.index') }}"
                        class="nav-link {{ request()->routeIs('cost-center.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person-lines-fill me-2"></i> Dashboard Cost Center
                    </a>
                </li>
            </div> --}}

            <div class="dropdown">
                <a href="javascript:void(0)"
                    class="mb-1 nav-link text-white dropdown-toggle {{ request()->is('companies*', 'kompartemens*', 'departemens*', 'cost-center*') ? 'active' : 'text-white' }}"
                    aria-expanded="{{ request()->is('companies*', 'kompartemens*', 'departemens*', 'cost-center*') ? 'true' : 'false' }}">
                    <i class="bi bi-building me-2"></i>
                    <span class="me-auto">MASTER DATA COMPANY</span>
                </a>
                <div
                    class="dropdown-content {{ request()->is('companies*', 'kompartemens*', 'departemens*', 'cost-center*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('companies.index') }}"
                            class="nav-link {{ request()->routeIs('companies.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-building me-2"></i> Company
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kompartemens.index') }}"
                            class="nav-link {{ request()->routeIs('kompartemens.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-diagram-3 me-2"></i> Kompartemen
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('departemens.index') }}"
                            class="nav-link {{ request()->routeIs('departemens.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-layers me-2"></i> Departemen
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('cost-center.index') }}"
                            class="nav-link {{ request()->routeIs('cost-center*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-cash-stack me-2"></i> Cost Center
                        </a>
                    </li>

                </div>
            </div>
        @endcan


        <!-- Additional Items... -->

        @can('data.view')
            <!-- MASTER DATA USER ACCESS -->
            <div class="dropdown">
                <a class="mb-1 nav-link dropdown-toggle {{ request()->is('job-roles*', 'composite-roles*', 'single-roles*', 'tcodes*') ? 'active' : 'text-white' }}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">MASTER DATA USER ACCESS</span>
                </a>
                <div
                    class="dropdown-content {{ request()->is('composite-roles*', 'single-roles*', 'tcodes*') ? 'show' : '' }}">

                    <li>
                        <a href="{{ route('job-roles.index') }}"
                            class="nav-link {{ request()->routeIs('job-roles.*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-badge me-2"></i> Job Roles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('composite-roles.index') }}"
                            class="nav-link {{ request()->routeIs('composite-roles.*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-people-fill me-2"></i> Composite Roles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('single-roles.index') }}"
                            class="nav-link {{ request()->routeIs('single-roles.*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-fill me-2"></i> Single Roles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tcodes.index') }}"
                            class="nav-link {{ request()->routeIs('tcodes.*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-code-slash me-2"></i> Tcodes
                        </a>
                    </li>
                </div>
            </div>


            <!-- MASTER DATA RELATIONSHIP -->
            <div class="dropdown">
                <a class="mb-1 nav-link dropdown-toggle {{ request()->is('relationship/job-composite*', 'relationship/composite-single*', 'relationship/single-tcode*') ? 'active' : 'text-white' }}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">MASTER DATA RELATIONSHIP</span>
                </a>

                <div class="dropdown-content {{ request()->is('relationship*') ? 'show' : '' }}">
                    {{-- <li class="nav-item">
                        <a href="{{ route('nik-job.index') }}"
                            class="nav-link {{ request()->routeIs('nik-job*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i> NIK - Job Role
                        </a>
                    </li> --}}
                    <li>
                        <a href="{{ route('job-composite.index') }}"
                            class="nav-link {{ request()->routeIs('job-composite*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-link-45deg"></i> Job Role - Composite
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('composite-single.index') }}"
                            class="nav-link {{ request()->routeIs('composite-single*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-link-45deg"></i> Composite - Single Role
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('single-tcode.index') }}"
                            class="nav-link {{ request()->routeIs('single-tcode*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-link-45deg"></i> Single Role - Tcodes
                        </a>
                    </li>
                </div>
            </div>



            <!-- IMPORT DATA -->
            @can('data.create')
                <div class="dropdown">
                    <a class="mb-1 nav-link dropdown-toggle {{ request()->is('company-kompartemen*', 'composite-single*', 'tcode-single-role*') ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i> <span class="me-auto">UPLOAD DATA MASTER</span>
                    </a>
                    <div
                        class="dropdown-content {{ request()->is('company_kompartemen*', 'composite_single*', 'tcode_single_role*') ? 'show' : '' }}">

                        <li>
                            <a href="{{ route('unit-kerja.upload-form') }}"
                                class="nav-link {{ request()->routeIs('unit-kerja.upload-form') ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload me-2"></i> Upload Unit Kerja
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('company_kompartemen.upload') }}"
                                class="nav-link {{ request()->routeIs('company_kompartemen*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Job Role - Composite Role
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('composite_single.upload') }}"
                                class="nav-link {{ request()->routeIs('composite_single*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Composite - Single Role
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tcode_single_role.upload') }}"
                                class="nav-link {{ request()->routeIs('tcode_single_role*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Single Role - Tcode
                            </a>
                        </li>
                    </div>
                </div>
            @endcan

            <hr>

            @can('Super Admin')
                <div>
                    <h5>MIDDLE DB</h5>
                </div>
                <li class="nav-item">
                    <a href="{{ route('middle_db.master_data_karyawan.index') }}"
                        class="mb-1 nav-link {{ request()->routeIs('middle_db.master_data_karyawan.*') ? 'active' : 'text-white' }}">
                        <i class="bi bi-people-fill me-2"></i> Master Data Karyawan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('middle_db.unit_kerja.index') }}"
                        class="mb-1 nav-link {{ request()->routeIs('middle_db.unit_kerja.*') ? 'active' : 'text-white' }}">
                        <i class="bi bi-diagram-3 me-2"></i> Unit Kerja
                    </a>
                </li>

                <div class="dropdown">
                    <a class="mb-1 nav-link dropdown-toggle {{ request()->routeIs('middle_db.usmm.*') ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ request()->routeIs('middle_db.usmm.*') ? 'true' : 'false' }}">
                        <i class="bi bi-folder-fill me-2"></i> Master USMM
                    </a>
                    <div class="dropdown-content {{ request()->routeIs('middle_db.usmm.*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('middle_db.usmm.index') }}"
                                class="mb-1 nav-link {{ request()->routeIs('middle_db.usmm.index') ? 'active' : 'text-white' }}">
                                <i class="bi bi-person-badge me-2"></i> Active
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('middle_db.usmm.inactive') }}"
                                class="mb-1 nav-link {{ request()->routeIs('middle_db.usmm.inactive') ? 'active' : 'text-white' }}">
                                <i class="bi bi-person-dash me-2"></i> Inactive
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('middle_db.usmm.expired') }}"
                                class="mb-1 nav-link {{ request()->routeIs('middle_db.usmm.expired') ? 'active' : 'text-white' }}">
                                <i class="bi bi-person-exclamation me-2"></i> Expired
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('middle_db.usmm.all') }}"
                                class="mb-1 nav-link {{ request()->routeIs('middle_db.usmm.all') ? 'active' : 'text-white' }}">
                                <i class="bi bi-people-fill me-2"></i> Full Data
                            </a>
                        </li>
                    </div>
                </div>

                <div class="dropdown">
                    <a class="mb-1 nav-link dropdown-toggle {{ request()->routeIs('middle_db.raw.*') ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ request()->routeIs('middle_db.raw.*') ? 'true' : 'false' }}">
                        <i class="bi bi-folder-fill me-2"></i> RAW
                    </a>
                    <div class="dropdown-content {{ request()->routeIs('middle_db.raw.*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('middle_db.raw.uam_relationship.index') }}"
                                class="mb-1 nav-link {{ request()->routeIs('middle_db.raw.uam_relationship.*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-diagram-3 me-2"></i> UAM Relationship Raw
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('middle_db.raw.generic_karyawan_mapping.index') }}"
                                class="mb-1 nav-link {{ request()->routeIs('middle_db.raw.generic_karyawan_mapping.*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-people-fill me-2"></i> Generic Karyawan Mapping Raw
                            </a>
                        </li>
                    </div>
                </div>
            @endcan

            <hr>

            <div>
                <h5>User & Cost Center</h5>
            </div>

            @php
                $modules = config('dynamic_uploads.modules');
            @endphp

            <div class="">
                <div class="dropdown">
                    <a class="mb-1 nav-link dropdown-toggle {{ request()->routeIs('periode*') ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ request()->routeIs('periode*') ? 'true' : 'false' }}">
                        <span class="me-auto">1. Buat Periode</span>
                    </a>
                    <div class="dropdown-content {{ request()->routeIs('periode*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('periode.index') }}"
                                class="nav-link {{ request()->routeIs('periode*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-calendar-event"></i> Periode
                            </a>
                        </li>
                    </div>
                </div>

                {{-- <div class="dropdown">
                <a class="nav-link text-white dropdown-toggle {{ request()->is('user-nik.upload*', 'master-data-nik.upload*', 'user-generic.upload*', 'nik_job_role.upload*') ? 'active' : 'text-white' }}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i><span class="me-auto"> UPLOAD DATA</span>
                </a>
                <div class="dropdown-content">
                    <li class="nav-item">
                        <a href="{{ route('user-nik.upload.form') }}"
                            class="nav-link {{ request()->routeIs('user-nik.upload.form') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-badge"></i> User NIK
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('master-data-nik.upload') }}"
                            class="nav-link {{ request()->routeIs('master-data-nik.upload') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-fill"></i> Master Data NIK
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('user-generic.upload') }}"
                            class="nav-link {{ request()->routeIs('user-generic.upload') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-lines-fill"></i> User Generic
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('nik_job_role.upload.form') }}"
                            class="nav-link {{ request()->routeIs('nik_job_role.upload.form') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i> NIK - Job Role
                        </a>
                    </li>
                </div>
            </div> --}}

                {{-- <div class="dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('dynamic_upload.*') ? 'active' : 'text-white' }}"
                        href="#" data-bs-toggle="dropdown" role="button"
                        aria-expanded="{{ request()->routeIs('dynamic_upload.*') ? 'true' : 'false' }}">
                        <i class="bi bi-cloud-upload me-2"></i> <span class="me-auto">DYNAMIC UPLOAD</span>
                    </a>
                    <div class="dropdown-content {{ request()->routeIs('dynamic_upload.*') ? 'show' : '' }}">
                        <ul class="nav flex-column ms-3">
                            @foreach ($modules as $key => $module)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dynamic_upload.*') && request()->route('module') === $key ? 'active' : 'text-white' }}"
                                        href="{{ route('dynamic_upload.upload', ['module' => $key]) }}">
                                        {{ $module['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div> --}}

                @php
                    $modules = config('dynamic_uploads.modules');

                    // Get the 'master_nik' module
                    $masterNikModule = $modules['master_nik'] ?? null;

                    // Get the 'user_nik' module
                    $userNikModule = $modules['user_nik'] ?? null;

                    // Get the 'nik_job_role' module
                    $nikJobRoleModule = $modules['nik_job_role'] ?? null;
                @endphp

                <div class="dropdown">
                    <a class="mb-1 nav-link dropdown-toggle {{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'master_nik') || request()->routeIs('user-detail*') ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'master_nik') || request()->routeIs('user-detail*') ? 'true' : 'false' }}">
                        <span class="me-auto">2. Master Data Karyawan</span>
                    </a>
                    <div
                        class="dropdown-content {{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'master_nik') || request()->routeIs('user-detail*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('dynamic_upload.upload', ['module' => 'master_nik']) }}"
                                class="nav-link {{ request()->route('module') === 'master_nik' ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload {{ $modules['master_nik']['name'] }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('user-detail.index') }}"
                                class="nav-link {{ request()->routeIs('user-detail*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-person-vcard"></i> User Detail
                            </a>
                        </li>
                    </div>
                </div>

                <div class="dropdown">
                    <a class="mb-1 nav-link dropdown-toggle {{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'user_nik') || request()->routeIs('user-nik.index') ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'user_nik') || request()->routeIs('user-nik.index') ? 'true' : 'false' }}">
                        3. Master Data USMM NIK
                    </a>
                    <div
                        class="dropdown-content {{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'user_nik') || request()->routeIs('user-nik.index') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('dynamic_upload.upload', ['module' => 'user_nik']) }}"
                                class="nav-link {{ request()->route('module') === 'user_nik' ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload {{ $modules['user_nik']['name'] }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('user-nik.index') }}"
                                class="nav-link {{ request()->routeIs('user-nik.index') ? 'active' : 'text-white' }}">
                                <i class="bi bi-person-badge"></i> User NIK
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a href="{{ route('user-nik.index_mixed') }}"
                                class="nav-link {{ request()->routeIs('user-nik.index_mixed*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-person-badge"></i> User NIK Mixed
                            </a>
                        </li> --}}
                    </div>
                </div>

                {{-- 4. Master Data USMM Generik --}}
                <div class="dropdown">
                    @php
                        $isUserGenericActive =
                            request()->routeIs('user-generic-unit-kerja.upload') ||
                            request()->routeIs('user-generic-unit-kerja.previewPage') ||
                            request()->routeIs('user-generic.upload') ||
                            request()->routeIs('user-generic.previewPage') ||
                            (request()->routeIs('user-generic.*') &&
                                !request()->routeIs('user-generic.upload') &&
                                !request()->routeIs('user-generic.previewPage') &&
                                !request()->routeIs('user-generic-job-role.*'));
                    @endphp
                    <a class="mb-1 nav-link dropdown-toggle {{ $isUserGenericActive ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ $isUserGenericActive ? 'true' : 'false' }}">
                        4. Master Data USMM Generik
                    </a>
                    <div class="dropdown-content {{ $isUserGenericActive ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('user-generic-unit-kerja.upload') }}"
                                class="nav-link {{ request()->routeIs('user-generic-unit-kerja.upload') || request()->routeIs('user-generic-unit-kerja.previewPage') ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload User Generic - Unit Kerja
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('user-generic.upload') }}"
                                class="nav-link {{ request()->routeIs('user-generic.upload') || request()->routeIs('user-generic.previewPage') ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload User Generic
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('user-generic.index') }}"
                                class="nav-link {{ request()->routeIs('user-generic.*') && !request()->routeIs('user-generic.upload') && !request()->routeIs('user-generic.previewPage') && !request()->routeIs('user-generic-job-role.*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-person-lines-fill"></i> User Generic
                            </a>
                        </li>
                    </div>
                </div>

                {{-- 5. Mapping USMM - Job Role --}}
                <div class="dropdown">
                    @php
                        $isMappingActive =
                            (request()->routeIs('dynamic_upload.upload') &&
                                request()->route('module') === 'nik_job_role') ||
                            request()->routeIs('user-generic-job-role.*') ||
                            request()->routeIs('nik-job*');
                    @endphp
                    <a class="mb-1 nav-link dropdown-toggle {{ $isMappingActive ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ $isMappingActive ? 'true' : 'false' }}">
                        5. Mapping USMM - Job Role
                    </a>
                    <div class="dropdown-content {{ $isMappingActive ? 'show' : '' }}">
                        {{-- <li class="nav-item">
                            <a href="{{ route('dynamic_upload.upload', ['module' => 'nik_job_role']) }}"
                                class="nav-link {{ request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'nik_job_role' ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload USMM - Job Role
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a href="{{ route('ussm-job-role.upload') }}"
                                class="nav-link {{ request()->routeIs('ussm-job-role.*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload USMM - Job Role
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('user-generic-job-role.index') }}"
                                class="nav-link {{ request()->routeIs('user-generic-job-role.*') && !request()->routeIs('user-generic-job-role.null-relationship') ? 'active' : 'text-white' }}">
                                <i class="bi bi-link-45deg"></i> User Generic - Job Role
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('user-generic-job-role.null-relationship') }}"
                                class="nav-link {{ request()->routeIs('user-generic-job-role.null-relationship') ? 'active' : 'text-white' }}">
                                <i class="bi bi-exclamation-circle"></i> User Generic Non Job
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('nik-job.index') }}"
                                class="nav-link {{ request()->routeIs('nik-job*') && !request()->routeIs('nik-job.null-relationship') ? 'active' : 'text-white' }}">
                                <i class="bi bi-link-45deg"></i> Relationship NIK - Job Role
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('nik-job.null-relationship') }}"
                                class="nav-link {{ request()->routeIs('nik-job.null-relationship') ? 'active' : 'text-white' }}">
                                <i class="bi bi-exclamation-circle"></i> User NIK Non Job
                            </a>
                        </li>
                    </div>
                </div>


                <hr>

                <div>
                    <h5>Report</h5>
                    <li class="nav-item">
                        <a href="{{ route('report.uar.index') }}"
                            class="mb-1 nav-link {{ request()->routeIs('report.uar.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-text me-2"></i> Report UAR
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('report.uam.index') }}"
                            class="mb-1 nav-link {{ request()->routeIs('report.uam.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-text me-2"></i> Report UAM
                        </a>
                    </li>
                </div>


                <hr>

            @endcan

            @can('manage company info')
                <div>
                    <h5>Master Data Parameter</h5>
                </div>
                <div>
                    <li class="nav-item">
                        <a href="{{ route('penomoran-uar.index') }}"
                            class="mb-1 nav-link {{ request()->routeIs('penomoran-uar*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-list-ol me-2"></i> Penomoran UAR
                        </a>
                    </li>
                </div>

                <div>
                    <li class="nav-item">
                        <a href="{{ route('penomoran-uam.index') }}"
                            class="mb-1 nav-link {{ request()->routeIs('penomoran-uam*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-list-ol me-2"></i> Penomoran UAM
                        </a>
                    </li>
                </div>

                <hr>
            @endcan

            {{-- Others --}}


            {{-- 
                    <hr>
                <li class="nav-item">
                    <a href="{{ route('prev-user.index') }}"
                        class="nav-link {{ request()->routeIs('prev-user.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person-lines-fill"></i> Generic Previous User
                    </a>
                </li> --}}

            {{-- <li class="nav-item">
                    <a href="{{ route('terminated-employee.index') }}"
                        class="nav-link {{ request()->routeIs('terminated-employee.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Terminated Employee
                    </a>
                </li> --}}

            {{-- <li class="nav-item">
                    <a href="{{ route('user-nik.compare') }}"
                        class="nav-link {{ request()->routeIs('user-nik.compare*') ? 'active' : 'text-white' }}">
                        <i class="bi bi-file-diff"></i> Compare User NIK Periode
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user-generic.compare') }}"
                        class="nav-link {{ request()->routeIs('user-generic.compare*') ? 'active' : 'text-white' }}">
                        <i class="bi bi-file-diff"></i> Compare User Generic Periode
                    </a>
                </li> --}}




            {{-- <a class="nav-link dropdown-toggle {{ request()->is('cost-center*') ? 'active' : 'text-white' }}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">MASTER DATA COST CENTER</span>
                </a>
                <div class="dropdown-content {{ request()->is('cost-center*') ? 'show' : '' }}">
                    <li class="nav-item">
                        <a href="{{ route('dashboard.user-generic') }}"
                            class="nav-link {{ request()->is('cost-center/user-generic/dashboard') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-bar-graph"></i> Dashboard Cost & User
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('cost-center.index') }}"
                            class="nav-link {{ request()->routeIs('cost-center*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-cash-stack"></i> Cost Center
                        </a>
                    </li>
                </div> --}}
            {{-- <li class="nav-item">
                <a href=""
                    class="nav-link {{ request()->routeIs('tcode_single_role*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-vector-pen"></i> User License
                </a>
            </li>
            </div>
            {{-- 
            <hr>

            <div>
                <h5 style="color: red">Report</h5>
            </div>

            <div>
                <li class="nav-item">
                    <a href="{{ route('report.unit') }}"
                        class="nav-link {{ request()->routeIs('report.unit') ? 'active' : 'text-white' }}">
                        <i class="bi bi-clipboard-data me-2"></i> Report Unit Kerja
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('report.filled-job-role.index') }}"
                        class="nav-link {{ request()->routeIs('report.filled-job-role.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i> Filled Job Role
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('report.empty-job-role.index') }}"
                        class="nav-link {{ request()->routeIs('report.empty-job-role.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i> Empty Job Role
                    </a>
                </div> --}}

            @can('manage access-matrix')
                <div>
                    <h5>Admin Menu</h5>
                </div>

                <div class="dropdown">
                    @php
                        $isAccessMatrixActive =
                            request()->routeIs('admin.access-matrix.roles.*') ||
                            request()->routeIs('admin.access-matrix.permissions.*') ||
                            request()->routeIs('adamin.access-matrix.index');
                    @endphp
                    <a class="mb-1 nav-link dropdown-toggle {{ $isAccessMatrixActive ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ $isAccessMatrixActive ? 'true' : 'false' }}">
                        <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">Access Matrix</span>
                    </a>
                    <div class="dropdown-content {{ $isAccessMatrixActive ? 'show' : '' }}">
                        <li>
                            <a href="{{ route('admin.access-matrix.roles.index') }}"
                                class="nav-link {{ request()->routeIs('admin.access-matrix.roles.*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-people me-2"></i> Assign Roles
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.access-matrix.permissions.index') }}"
                                class="nav-link {{ request()->routeIs('admin.access-matrix.permissions.*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-shield-check me-2"></i> Assign Permissions
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.access-matrix.index') }}"
                                class="nav-link {{ request()->routeIs('admin.access-matrix.index') ? 'active' : 'text-white' }}">
                                <i class="bi bi-table me-2"></i> Access Matrix (Legacy)
                            </a>
                        </li>
                    </div>
                </div>

                @role('Super Admin')
                    <li>
                        <a href="{{ route('admin.email-change-requests.index') }}"
                            class="mb-1 nav-link {{ request()->routeIs('admin.email-change-requests.*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-envelope-check me-2"></i> Email Change Requests
                        </a>
                    </li>
                @endrole
            @endcan

            {{-- @role('Super Admin')
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person-lock me-2"></i> Admin Page
                    </a>
                </li>
            @endrole --}}

            @can('manage users')
                <li>
                    <a href="{{ route('users.index') }}"
                        class="mb-1 nav-link {{ request()->routeIs('users.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person-lines-fill me-2"></i> Manage Users
                    </a>
                </li>
            @endcan

            {{-- Dynamic spacer: pushes the bottom of the list up only when it overflows --}}
            <li class="sidebar-spacer" aria-hidden="true"></li>
    </ul>
</div>
