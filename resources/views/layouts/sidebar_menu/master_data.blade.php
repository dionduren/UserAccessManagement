<hr>
{{-- <div class="mx-1">
                <li>
                    <a href="{{ route('cost-center.index') }}"
                        class="nav-link {{ request()->routeIs('cost-center.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person-lines-fill me-2"></i> Dashboard Cost Center
                    </a>
                </li>
            </div> --}}

<div>
    <h5>MASTER DATA - UAM</h5>
</div>

@php
    $isMasterDataOrganisasiActive = request()->routeIs(
        'companies.*',
        'kompartemens.*',
        'departemens.*',
        'cost-center.*',
        'middle_db.unit_kerja.*',
        'middle_db.master_dat_karyawan.*',
        'compare.unit_kerja', // Added compare routes
    );
@endphp
<div class="dropdown">
    <a href="javascript:void(0)"
        class="mb-1 nav-link text-white dropdown-toggle {{ $isMasterDataOrganisasiActive ? 'active' : 'text-white' }}"
        aria-expanded="{{ $isMasterDataOrganisasiActive ? 'true' : 'false' }}">
        <i class="bi bi-building me-2"></i>
        <span class="me-auto">MASTER DATA ORGANISASI</span>
    </a>

    <div class="dropdown-content {{ $isMasterDataOrganisasiActive ? 'show' : '' }}">
        @can('Super Admin')
            <div class="mx-3 text-white text-end"><strong>Middle DB</strong></div>
            @php
                $isMdbUnitKerjaActive = request()->routeIs('middle_db.unit_kerja.*', 'compare.unit_kerja');
            @endphp
            <li class="nav-item">
                <a href="{{ route('middle_db.unit_kerja.index') }}"
                    class="mb-1 nav-link {{ $isMdbUnitKerjaActive ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i> All Unit Kerja
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('middle_db.master_data_karyawan.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.master_data_karyawan.*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>Unit Kerja - Karyawan
                </a>
            </li>
            <hr width="80%" class="my-1" style="margin-left: auto">
        @endcan

        <div class="mx-3 text-white text-end"><strong>Local Data</strong></div>
        <li>
            <a href="javascript:void(0)" class="nav-link">
                <span class="text-danger">
                    <i class="bi bi-cloud-download me-2"></i>Import Data Organisasi
                </span>
            </a>
        </li>
        <li>
            <a href="{{ route('unit-kerja.upload-form') }}"
                class="nav-link {{ request()->routeIs('unit-kerja.upload-form') ? 'active' : 'text-white' }}">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload Data Organisasi
            </a>
        </li>
        <li>
            <a href="{{ route('companies.index') }}"
                class="nav-link {{ request()->routeIs('companies.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-building me-2"></i> Company
            </a>
        </li>
        <li>
            <a href="{{ route('kompartemens.index') }}"
                class="nav-link {{ request()->routeIs('kompartemens.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-diagram-3 me-2"></i> Kompartemen
            </a>
        </li>
        <li>
            <a href="{{ route('departemens.index') }}"
                class="nav-link {{ request()->routeIs('departemens.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-layers me-2"></i> Departemen
            </a>
        </li>

        <li>
            <a href="{{ route('cost-center.index') }}"
                class="nav-link {{ request()->routeIs('cost-center.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-cash-stack me-2"></i> Cost Center
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link">
                <span class="text-danger">
                    <i class="bi bi-cloud-download me-2"></i>Unit Kerja - Karyawan
                </span>
            </a>
        </li>
    </div>
</div>



<!-- Additional Items... -->

<!-- MASTER DATA ROLE -->
<div class="dropdown">
    @php
        $isMasterRoleActive = request()->routeIs(
            'composite-roles.*',
            'single-roles.*',
            'tcodes.*',
            'middle_db.uam.composite_role.*',
            'middle_db.uam.single_role.*',
            'middle_db.uam.tcode.*',
            'compare.uam.composite_single.*',
            'compare.uam.single.*',
            'compare.uam.tcode.*',
        );
    @endphp
    <a class="mb-1 nav-link dropdown-toggle {{ $isMasterRoleActive ? 'active' : 'text-white' }}"
        data-bs-toggle="dropdown" href="#" role="button"
        aria-expanded="{{ $isMasterRoleActive ? 'true' : 'false' }}">
        <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">MASTER DATA ROLE</span>
    </a>
    <div class="dropdown-content {{ $isMasterRoleActive ? 'show' : '' }}">

        @can('Super Admin')
            <div class="mx-3 text-white text-end"><strong>Middle DB</strong></div>

            <li class="nav-item">
                <a href="{{ route('middle_db.uam.tcode.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.uam.tcode.*', 'compare.uam.tcode*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>Tcode
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('middle_db.uam.single_role.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.uam.single_role.*', 'compare.uam.single*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>Single Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('middle_db.view.uam.single_tcode.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.single_tcode.*', 'compare.uam.relationship.single_tcode*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>Single Role - Tcode
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('middle_db.uam.composite_role.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.uam.composite_role.*', 'compare.uam.composite*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>Composite Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('middle_db.view.uam.composite_single.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.composite_single.*', 'compare.uam.relationship.composite_single*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>Composite Role - Single Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('middle_db.view.uam.composite_ao.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.composite_ao.*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>Composite Role - AO
                </a>
            </li>
            <hr width="80%" class="my-1" style="margin-left: auto">
        @endcan
        <div class="mx-3 text-white text-end"><strong>Local Data</strong></div>
        <li>
            <a href="javascript:void(0)" class="nav-link">
                <span class="text-danger">
                    <i class="bi bi-cloud-download me-2"></i>Import Master Data Role
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('composite_single.upload') }}"
                class="nav-link {{ request()->routeIs('composite_single*') ? 'active' : 'text-white' }}">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload Composite - Single Role
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('tcode_single_role.upload') }}"
                class="nav-link {{ request()->routeIs('tcode_single_role*') ? 'active' : 'text-white' }}">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload Single Role - Tcode
            </a>
        </li>

        <li>
            <a href="{{ route('tcodes.index') }}"
                class="nav-link {{ request()->routeIs('tcodes.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-code-slash me-2"></i>Tcodes
            </a>
        </li>
        <li>
            <a href="{{ route('single-roles.index') }}"
                class="nav-link {{ request()->routeIs('single-roles.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-person-fill me-2"></i>Single Roles
            </a>
        </li>
        <li>
            <a href="{{ route('single-tcode.index') }}"
                class="nav-link {{ request()->routeIs('single-tcode*') ? 'active' : 'text-white' }}">
                <i class="bi bi-link-45deg me-2"></i>Single Role - Tcode
            </a>
        </li>

        <li>
            <a href="{{ route('composite-roles.index') }}"
                class="nav-link {{ request()->routeIs('composite-roles.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-people-fill me-2"></i>Composite Roles
            </a>
        </li>
        <li>
            <a href="{{ route('composite-single.index') }}"
                class="nav-link {{ request()->routeIs('composite-single*') ? 'active' : 'text-white' }}">
                <i class="bi bi-link-45deg me-2"></i>Composite - Single Role
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link">
                <span class="text-danger">
                    <i class="bi bi-link-45deg me-2"></i>Composite Role - AO
                </span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link">
                <span class="text-danger">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload Company - Composite
                </span>
            </a>
        </li>
    </div>
</div>

<!-- User ID & Job Role -->
@php
    $modules = config('dynamic_uploads.modules');
@endphp

<div class="dropdown">
    @php
        $isUserIdJobRoleActive = request()->routeIs(
            'job-composite*',
            'job-roles.*',
            'user-nik.*',
            'user-nik.middle_db',
            'user-generic.*',
            'user-generic.upload',
            'user-generic.previewPage',
            'dynamic_upload.upload',
            'company_kompartemen.*',
            'middle_db.view.uam.user_composite.*',
            'compare.uam.relationship.user_composite*',
        );
    @endphp
    <a class="mb-1 nav-link dropdown-toggle {{ $isUserIdJobRoleActive ? 'active' : 'text-white' }}"
        data-bs-toggle="dropdown" href="#" role="button"
        aria-expanded="{{ $isUserIdJobRoleActive ? 'true' : 'false' }}">
        <i class="bi bi-folder-fill me-2"></i> <span class="me-auto">USER ID & JOB ROLE</span>
    </a>

    <div class="dropdown-content {{ $isUserIdJobRoleActive ? 'show' : '' }}">
        @can('Super Admin')
            <div class="mx-3 text-white text-end"><strong>Middle DB</strong></div>
            <li class="nav-item">
                <a href="{{ route('user-nik.middle_db') }}"
                    class="mb-1 nav-link {{ request()->routeIs('user-nik.middle_db') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>User ID NIK
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('user-generic.middle_db') }}"
                    class="mb-1 nav-link {{ request()->routeIs('user-generic.middle_db') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>User ID Generic
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('middle_db.view.uam.user_composite.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.user_composite.*', 'compare.uam.relationship.user_composite*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-database-fill-gear me-2"></i>User ID - Composite Role
                </a>
            </li>
        @endcan
        <hr width="80%" class="my-1" style="margin-left: auto">
        <div class="mx-3 text-white text-end"><strong>Manage Local Data</strong></div>
        @can('data.create')
            <li class="nav-item">
                <a href="{{ route('company_kompartemen.upload') }}"
                    class="nav-link {{ request()->routeIs('company_kompartemen*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload Job Role - Composite
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('dynamic_upload.upload', ['module' => 'user_nik']) }}"
                    class="nav-link {{ request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'user_nik' ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload {{ $modules['user_nik']['name'] }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('user-generic.upload') }}"
                    class="nav-link {{ request()->routeIs('user-generic.upload') || request()->routeIs('user-generic.previewPage') ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload User ID Generic
                </a>
            </li>
            <hr width="80%" class="my-1" style="margin-left: auto">
        @endcan
        <div class="mx-3 text-white text-end"><strong>Local Data</strong></div>
        <li>
            <a href="{{ route('job-roles.index') }}"
                class="nav-link {{ request()->routeIs('job-roles.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-person-badge me-2"></i>Job Roles
            </a>
        </li>
        <li>
            <a href="{{ route('job-composite.index') }}"
                class="nav-link {{ request()->routeIs('job-composite*') ? 'active' : 'text-white' }}">
                <i class="bi bi-link-45deg me-2"></i>Job Role - Composite
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('user-nik.index') }}"
                class="nav-link {{ request()->routeIs('user-nik.index') ? 'active' : 'text-white' }}">
                <i class="bi bi-person-badge me-2"></i>User ID NIK
            </a>
        </li>
        @php
            $isUserGenericIndexActive =
                request()->routeIs('user-generic.*') &&
                !request()->routeIs('user-generic.upload') &&
                !request()->routeIs('user-generic.previewPage') &&
                !request()->routeIs('user-generic-job-role.*') &&
                !request()->routeIs('user-generic.middle_db');
        @endphp
        <li class="nav-item">
            <a href="{{ route('user-generic.index') }}"
                class="nav-link {{ $isUserGenericIndexActive ? 'active' : 'text-white' }}">
                <i class="bi bi-person-lines-fill me-2"></i>User ID Generic
            </a>
        </li>
    </div>
</div>
