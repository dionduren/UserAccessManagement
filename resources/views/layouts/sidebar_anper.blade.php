@can('data.view')
    <hr>

    <div>
        <h5>UAM MANAGEMENT</h5>
    </div>
    <div class="mx-3 text-white text-end"><strong>UPLOAD DATA MAPPING</strong></div>
    <li class="nav-item">
        <a href="{{ route('composite_single.upload') }}"
            class="nav-link {{ request()->routeIs('composite_single*') ? 'active' : 'text-white' }}">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload Composite - Single Role
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('company_kompartemen.upload') }}"
            class="nav-link {{ request()->routeIs('company_kompartemen*') ? 'active' : 'text-white' }}">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload Job Role - Composite
        </a>
    </li>

    <hr width="80%" class="my-1" style="margin-left: auto">
    <div class="mx-3 text-white text-end"><strong>Proses UAM</strong></div>

    <div class="dropdown">
        @php
            $isMasterDataOrganisasiActive = request()->routeIs('kompartemens.*', 'departemens.*', 'cost-center.*');
        @endphp
        <a class="mb-1 nav-link dropdown-toggle {{ '' ? 'active' : 'text-white' }}" data-bs-toggle="dropdown" href="#"
            role="button" aria-expanded="{{ '' ? 'true' : 'false' }}">
            <span class="me-auto">1. Validasi Data Organisasi</span>
        </a>
        <div class="dropdown-content">
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
        </div>
    </div>

    <div class="dropdown">
        @php
            $isMasterRoleActive = request()->routeIs([
                'tcodes.*',
                'single-roles.*',
                'single-tcode*',
                'composite-roles.*',
                'composite-single*',
            ]);
        @endphp
        <a class="mb-1 nav-link dropdown-toggle {{ $isMasterRoleActive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isMasterRoleActive ? 'true' : 'false' }}">
            <span class="me-auto">2. Validasi Data Role</span>
        </a>
        <div class="dropdown-content {{ $isMasterRoleActive ? 'show' : '' }}">
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
        </div>
    </div>

    @php
        $isJobRoleValidationActive = request()->routeIs('job-roles.*', 'job-composite*');
    @endphp
    <div class="dropdown">
        <a class="mb-1 nav-link dropdown-toggle {{ $isJobRoleValidationActive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isJobRoleValidationActive ? 'true' : 'false' }}">
            <span class="me-auto">3. Validasi Data Job Role</span>
        </a>
        <div class="dropdown-content {{ $isJobRoleValidationActive ? 'show' : '' }}">
            <li>
                <a href="{{ route('job-roles.index') }}"
                    class="nav-link {{ request()->routeIs('job-roles*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-person-badge me-2"></i>Unit Kerja - Job Roles
                </a>
            </li>
            <li>
                <a href="{{ route('job-composite.index') }}"
                    class="nav-link {{ request()->routeIs('job-composite*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-link-45deg me-2"></i>Job Role - Composite Role
                </a>
            </li>
        </div>
    </div>

    <li class="nav-item">
        <a href="{{ route('report.uam.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('report.uam.index') ? 'active' : 'text-white' }}">
            <i class="bi bi-file-earmark-text me-2"></i> Report UAM
        </a>
    </li>
    {{--   =======================================================  --}}

    <hr>
    <div>
        <h5>UAR MANAGEMENT</h5>
    </div>
    <div class="mx-3 text-white text-end"><strong>DATA UAR</strong></div>
    @php
        $isUserGenericUnitUploadActive = request()->routeIs([
            'user-generic-unit-kerja.upload',
            'user-generic-unit-kerja.previewPage',
        ]);

    @endphp

    <li class="nav-item">
        <a href="{{ route('user-generic-unit-kerja.upload') }}"
            class="nav-link {{ $isUserGenericUnitUploadActive ? 'active' : 'text-white' }}">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload User ID Generic - Unit Kerja
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('ussm-job-role.upload') }}"
            class="nav-link {{ request()->routeIs('ussm-job-role.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload User ID - Job Role
        </a>
    </li>

    <hr width="80%" class="my-1" style="margin-left: auto">
    <div class="mx-3 text-white text-end"><strong>Proses UAM</strong></div>

    <div class="dropdown">
        <a class="mb-1 nav-link dropdown-toggle {{ request()->routeIs('periode*') ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ request()->routeIs('periode*') ? 'true' : 'false' }}">
            <span class="me-auto">1. Cek Info Periode</span>
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

    <div class="dropdown">
        <a class="mb-1 nav-link dropdown-toggle {{ request()->routeIs('report.ba_penarikan*') ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ request()->routeIs('report.ba_penarikan*') ? 'true' : 'false' }}">
            <span class="me-auto">2. Buat Berita Acara Penarikan Data</span>
        </a>
        <div class="dropdown-content {{ request()->routeIs('report.ba_penarikan*') ? 'show' : '' }}">
            <li class="nav-item">
                <a href="{{ route('report.ba_penarikan.index') }}"
                    class="mb-1 nav-link {{ request()->routeIs('report.ba_penarikan.index') ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-text me-2"></i> Report BA Penarikan Data
                </a>
            </li>
        </div>
    </div>

    <div class="dropdown">
        <a class="mb-1 nav-link dropdown-toggle {{ '' ? 'active' : 'text-white' }}" data-bs-toggle="dropdown"
            href="#" role="button" aria-expanded="{{ '' ? 'true' : 'false' }}">
            <span class="me-auto">3. Validasi Data User ID</span>
        </a>
        <div class="dropdown-content">
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


    <div class="dropdown">
        @php
            $isMappingUserNIKUnitKerjaActive = request()->routeIs('unit_kerja.user_nik.*');
            $isMappingUserGenericUnitKerjaActive = request()->routeIs('unit_kerja.user_generic.*');
            $isMappingUserIDUnitKerjaActive = $isMappingUserNIKUnitKerjaActive || $isMappingUserGenericUnitKerjaActive;
        @endphp
        <a class="mb-1 nav-link dropdown-toggle {{ $isMappingUserIDUnitKerjaActive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isMappingUserIDUnitKerjaActive ? 'true' : 'false' }}">
            <span class="me-auto">4. Validasi Map User ID - Unit Kerja</span>
        </a>
        <div class="dropdown-content">
            <li class="nav-item">
                <a href="{{ route('unit_kerja.user_generic.index') }}"
                    class="nav-link text-white {{ $isMappingUserGenericUnitKerjaActive ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill me-2"></i>User ID Generic - Unit Kerja
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('unit_kerja.user_nik.index') }}"
                    class="nav-link text-white {{ $isMappingUserNIKUnitKerjaActive ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill me-2"></i>User ID NIK - Unit Kerja
                </a>
            </li>
        </div>
    </div>


    <div class="dropdown">
        @php

            $nikJobNullActive = request()->routeIs('nik-job.null-relationship');
            $genericJobNullActive = request()->routeIs('user-generic-job-role.null-relationship');

            $nikJobActive = request()->routeIs('nik-job.*') && !$nikJobNullActive;
            $genericJobActive = request()->routeIs('user-generic-job-role.*') && !$genericJobNullActive;

            $isMappingUserIDJobRoleActive =
                $nikJobActive || $genericJobActive || $nikJobNullActive || $genericJobNullActive;
        @endphp
        <a class="mb-1 nav-link dropdown-toggle {{ $isMappingUserIDJobRoleActive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isMappingUserIDJobRoleActive ? 'true' : 'false' }}">
            <span class="me-auto">5. Validasi Map User ID - Job Role</span>
        </a>
        <div class="dropdown-content {{ $isMappingUserIDJobRoleActive ? 'show' : '' }}">

            <li class="nav-item">
                <a href="{{ route('nik-job.index') }}" class="nav-link {{ $nikJobActive ? 'active' : 'text-white' }}">
                    <i class="bi bi-link-45deg"></i> User ID NIK - Job Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('user-generic-job-role.index') }}"
                    class="nav-link {{ $genericJobActive ? 'active' : 'text-white' }}">
                    <i class="bi bi-link-45deg"></i> User ID Generic - Job Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('nik-job.null-relationship') }}"
                    class="nav-link {{ $nikJobNullActive ? 'active' : 'text-white' }}">
                    <i class="bi bi-exclamation-circle"></i> User ID NIK Non Job
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('user-generic-job-role.null-relationship') }}"
                    class="nav-link {{ $genericJobNullActive ? 'active' : 'text-white' }}">
                    <i class="bi bi-exclamation-circle"></i> User ID Generic Non Job
                </a>
            </li>
        </div>
    </div>



    <li class="nav-item">
        <a href="{{ route('report.uar.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('report.uar.index') ? 'active' : 'text-white' }}">
            <i class="bi bi-file-earmark-text me-2"></i> Report UAR
        </a>
    </li>

    {{-- <hr>

    <div>
        <h5>Report</h5>
    </div>

    @include('layouts.sidebar_menu_anper.report') --}}
@endcan
