<div class="sidebar-heading mt-2">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4"><i class="bi bi-h-square"></i> Hak Akses</span>
    </a>
</div>
<hr>
<div class="list-group list-group-flush overflow-auto h-100 no-scrollbar">
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : 'text-white' }}">
                <i class="bi bi-house-door me-2"></i> Home
            </a>
        </li>


        <!-- Other Sidebar Links -->
        @can('manage company info')
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
                    class="nav-link text-white dropdown-toggle {{ request()->is('companies*', 'kompartemens*', 'departemens*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-building me-2"></i> <span class="me-auto">MASTER DATA COMPANY</span>
                </a>
                <div
                    class="dropdown-content {{ request()->is('companies*', 'kompartemens*', 'departemens*') ? 'show' : '' }}">
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

                </div>
            </div>
        @endcan


        <!-- Additional Items... -->

        @can('manage roles')
            <!-- MASTER DATA USER ACCESS -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle {{ request()->is('job-roles*', 'composite-roles*', 'single-roles*', 'tcodes*') ? 'active' : 'text-white' }}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">MASTER DATA USER ACCESS</span>
                </a>
                <div
                    class="dropdown-content {{ request()->is('composite-roles*', 'single-roles*', 'tcodes*') ? 'show' : '' }}">

                    <li>
                        <a href="{{ route('job-roles.index') }}"
                            class="nav-link {{ request()->routeIs('job-roles.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-badge me-2"></i> Job Roles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('composite-roles.index') }}"
                            class="nav-link {{ request()->routeIs('composite-roles.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-people-fill me-2"></i> Composite Roles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('single-roles.index') }}"
                            class="nav-link {{ request()->routeIs('single-roles.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-fill me-2"></i> Single Roles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tcodes.index') }}"
                            class="nav-link {{ request()->routeIs('tcodes.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-code-slash me-2"></i> Tcodes
                        </a>
                    </li>
                </div>
            </div>


            <!-- MASTER DATA RELATIONSHIP -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle {{ request()->is('relationship/job-composite*', 'relationship/composite-single*', 'relationship/single-tcode*') ? 'active' : 'text-white' }}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">MASTER DATA RELATIONSHIP</span>
                </a>

                <div class="dropdown-content {{ request()->is('relationship*') ? 'show' : '' }}">
                    <li class="nav-item">
                        <a href="{{ route('nik-job.index') }}"
                            class="nav-link {{ request()->routeIs('nik-job*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i> NIK - Job Role
                        </a>
                    </li>
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
            <div class="dropdown">
                <a class="nav-link dropdown-toggle {{ request()->is('company-kompartemen*', 'composite-single*', 'tcode-single-role*') ? 'active' : 'text-white' }}"
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

            <hr>

            <div>
                <h5>User & Cost Center</h5>
            </div>

            @php
                $modules = config('dynamic_uploads.modules');
            @endphp

            <div class="">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('periode*') ? 'active' : 'text-white' }}"
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
                    <a class="nav-link dropdown-toggle {{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'master_nik') || request()->routeIs('user-detail*') ? 'active' : 'text-white' }}"
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
                    <a class="nav-link dropdown-toggle {{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'user_nik') || request()->routeIs('user-nik.index') ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ (request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'user_nik') || request()->routeIs('user-nik.index') ? 'true' : 'false' }}">
                        3. Master Data USSM NIK
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

                {{-- 4. Master Data USSM Generik --}}
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
                    <a class="nav-link dropdown-toggle {{ $isUserGenericActive ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ $isUserGenericActive ? 'true' : 'false' }}">
                        4. Master Data USSM Generik
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

                {{-- 5. Mapping USSM - Job Role --}}
                <div class="dropdown">
                    @php
                        $isMappingActive =
                            (request()->routeIs('dynamic_upload.upload') &&
                                request()->route('module') === 'nik_job_role') ||
                            request()->routeIs('user-generic-job-role.*') ||
                            request()->routeIs('nik-job*');
                    @endphp
                    <a class="nav-link dropdown-toggle {{ $isMappingActive ? 'active' : 'text-white' }}"
                        data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="{{ $isMappingActive ? 'true' : 'false' }}">
                        5. Mapping USSM - Job Role
                    </a>
                    <div class="dropdown-content {{ $isMappingActive ? 'show' : '' }}">
                        {{-- <li class="nav-item">
                            <a href="{{ route('dynamic_upload.upload', ['module' => 'nik_job_role']) }}"
                                class="nav-link {{ request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'nik_job_role' ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload USSM - Job Role
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a href="{{ route('ussm-job-role.upload') }}"
                                class="nav-link {{ request()->routeIs('ussm-job-role.*') ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload USSM - Job Role
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
                            class="nav-link {{ request()->routeIs('report.uar.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-text me-2"></i> Report UAR
                        </a>
                    </li>
                </div>


                <hr>

                <div>
                    <h5>Master Data Parameter</h5>
                </div>
                <div>
                    <li class="nav-item">
                        <a href="{{ route('penomoran-uar.index') }}"
                            class="nav-link {{ request()->routeIs('penomoran-uar*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-list-ol me-2"></i> Penomoran UAR
                        </a>
                    </li>
                </div>

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
                </li>

                <a class="nav-link dropdown-toggle {{ request()->is('cost-center*') ? 'active' : 'text-white' }}"
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
                </li>
            </div> --}}



            @endcan
            <hr>

            @can('manage access-matrix')
                <div>
                    <h5>Admin Menu</h5>
                </div>

                <li>
                    <a href="{{ route('access-matrix') }}"
                        class="nav-link {{ request()->routeIs('access-matrix') ? 'active' : 'text-white' }}">
                        <i class="bi bi-table me-2"></i> Access Matrix
                    </a>
                </li>
            @endcan

            @role('Admin')
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person-lock me-2"></i> Admin Page
                    </a>
                </li>
            @endrole

            @can('manage users')
                <li>
                    <a href="{{ route('users.index') }}"
                        class="nav-link {{ request()->routeIs('users.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person-lines-fill me-2"></i> Manage Users
                    </a>
                </li>
            @endcan
    </ul>
</div>

{{-- <hr> --}}

<!-- Profile Section -->
{{-- <div class="dropdown mb-1" style="margin-left: 10px;margin-right:10px">
    @auth
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
            id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ Auth::user()->profile_photo_url ?? 'https://randomuser.me/api/portraits/men/64.jpg' }}"
                alt="" width="32" height="32" class="rounded-circle me-2">
            <strong>{{ Auth::user()->name }}</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
            <li><a class="dropdown-item">Profile</a></li>
            <li><a class="dropdown-item">Settings</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a></li>
        </ul>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    @else
        <a href="{{ route('login') }}" class="btn btn-primary w-100">Login</a>
    @endauth
</div> --}}
