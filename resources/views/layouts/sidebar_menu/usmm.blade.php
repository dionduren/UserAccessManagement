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
        $nikJobRoleModule = $modules['nik_job_role'] ?? null;

        // $isUploadMasterNikActive =
        //     request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'master_nik';
        // $isUploadUserNikActive =
        //     request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'user_nik';
        $isUserGenericUnitUploadActive = request()->routeIs([
            'user-generic-unit-kerja.upload',
            'user-generic-unit-kerja.previewPage',
        ]);
        $isUserGenericUnitActive = request()->routeIs('unit_kerja.user_generic.*');
        $isUserDetailActive = request()->routeIs('user-detail.*');
        $isImportNIKUnitActive = request()->routeIs('import.nik_unit_kerja.*');

        $isUserIdUnitActive =
            // $isUploadMasterNikActive ||
            // $isUploadUserNikActive ||
            $isUserGenericUnitUploadActive || $isUserGenericUnitActive || $isUserDetailActive;
    @endphp

    <div class="dropdown">
        <a class="mb-1 nav-link dropdown-toggle {{ $isUserIdUnitActive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isUserIdUnitActive ? 'true' : 'false' }}">
            <span class="me-auto">2. Validasi User ID - Unit Kerja</span>
        </a>
        <div class="dropdown-content {{ $isUserIdUnitActive ? 'show' : '' }}">
            @can('Super Admin')
                <div class="mx-3 text-white text-end"><strong>Middle DB</strong></div>

                <li>
                    <a href="{{ route('import.nik_unit_kerja.index') }}"
                        class="nav-link text-white {{ $isImportNIKUnitActive ? 'active' : 'text-white' }}">
                        <i class="bi bi-cloud-download me-2"></i>Import User ID NIK - Unit Kerja
                    </a>
                </li>
                <hr width="80%" class="my-1" style="margin-left: auto">
            @endcan
            <div class="mx-3 text-white text-end"><strong>Local Data</strong></div>

            {{-- 
            <li>
                <a href="javascript:void(0)" class="nav-link text-danger disabled">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload User ID NIK - Unit Kerja
                </a>
            </li> --}}

            <li class="nav-item">
                <a href="{{ route('user-generic-unit-kerja.upload') }}"
                    class="nav-link {{ $isUserGenericUnitUploadActive ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload User ID Generic - Unit Kerja
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('unit_kerja.user_generic.index') }}"
                    class="nav-link text-white {{ $isUserGenericUnitActive ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill me-2"></i>User ID Generic - Unit Kerja
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('unit_kerja.user_nik.index') }}"
                    class="nav-link text-white {{ request()->routeIs('unit_kerja.user_nik.index') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill me-2"></i>User ID NIK - Unit Kerja
                </a>
            </li>
            {{-- <li class="nav-item">
                <a href="{{ route('dynamic_upload.upload', ['module' => 'master_nik']) }}"
                    class="nav-link {{ $isUploadMasterNikActive ? 'active' : 'text-white' }}">
                    <i class="bi bi-cloud-upload"></i> Upload {{ $modules['master_nik']['name'] }}
                </a>
            </li> --}}
            {{-- <li class="nav-item">
                <a href="{{ route('user-detail.index') }}"
                    class="nav-link {{ request()->routeIs('user-detail.*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-person-vcard"></i> User Detail
                </a>
            </li> --}}
            {{-- @can('Super Admin')
                <hr width="80%" class="my-1" style="margin-left: auto">
                <div class="mx-3 text-white text-end"><strong>Middle DB</strong></div>
                <li class="nav-item">
                    <a href="{{ route('middle_db.master_data_karyawan.index') }}"
                        class="mb-1 nav-link {{ request()->routeIs('middle_db.master_data_karyawan.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-database-fill-gear me-2"></i>Master Data Karyawan
                    </a>
                </li>
            @endcan --}}
        </div>
    </div>

    {{-- <div class="dropdown">
        @php
            $isMasterDataUSMMNIKactive = request()->routeIs('dynamic_upload.upload', 'user-nik*');
        @endphp

        <a class="mb-1 nav-link dropdown-toggle {{ $isMasterDataUSMMNIKactive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isMasterDataUSMMNIKactive ? 'true' : 'false' }}">
            3. Validasi User ID NIK - Unit Kerja
        </a>
        <div class="dropdown-content {{ $isMasterDataUSMMNIKactive ? 'show' : '' }}">

            <li class="nav-item">
                <a href="{{ route('dynamic_upload.upload', ['module' => 'user_nik']) }}"
                    class="nav-link {{ request()->route('module') === 'user_nik' ? 'active' : 'text-white' }}">
                    <i class="bi bi-cloud-upload"></i> Upload {{ $modules['user_nik']['name'] }}
                </a>
            </li>

        </div>
    </div>

    <div class="dropdown">
        @php
            // $isUserGenericActive =
            //     request()->routeIs([
            //         'user-generic-unit-kerja.upload',
            //         'user-generic-unit-kerja.previewPage',
            //         // 'user-generic.upload',
            //         // 'user-generic.previewPage',
            //     ]) ||
            //     (request()->routeIs('user-generic.*') &&
            //         !request()->routeIs([
            //             'user-generic.upload',
            //             'user-generic.previewPage',
            //             'user-generic-job-role.*',
            //             'user-generic.middle_db',
            //         ]));
        @endphp
        <a class="mb-1 nav-link dropdown-toggle {{ $isUserGenericActive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isUserGenericActive ? 'true' : 'false' }}">
            4. Validasi User ID Generic - Unit Kerja
        </a>
        <div class="dropdown-content {{ $isUserGenericActive ? 'show' : '' }}">

        </div>
    </div> --}}

    {{-- 5. Mapping USMM - Job Role --}}
    <div class="dropdown">
        @php
            $isMappingActive = request()->routeIs(
                // 'dynamic_upload.upload',
                'nik_job_role',
                'user-generic-job-role.*',
                'nik-job*',
                'mapping.middle_db.user_generic_uam',
            );
        @endphp
        <a class="mb-1 nav-link dropdown-toggle {{ $isMappingActive ? 'active' : 'text-white' }}"
            data-bs-toggle="dropdown" href="#" role="button"
            aria-expanded="{{ $isMappingActive ? 'true' : 'false' }}">
            3. Validasi User ID - Job Role
        </a>
        <div class="dropdown-content {{ $isMappingActive ? 'show' : '' }}">
            <div class="mx-3 text-white text-end"><strong>Local Data</strong></div>
            {{-- <li class="nav-item">
                            <a href="{{ route('dynamic_upload.upload', ['module' => 'nik_job_role']) }}"
                                class="nav-link {{ request()->routeIs('dynamic_upload.upload') && request()->route('module') === 'nik_job_role' ? 'active' : 'text-white' }}">
                                <i class="bi bi-cloud-upload"></i> Upload USMM - Job Role
                            </a>
                        </li> --}}
            <li class="nav-item">
                <a href="{{ route('ussm-job-role.upload') }}"
                    class="nav-link {{ request()->routeIs('ussm-job-role.*') ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload User ID - Job Role
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('nik-job.index') }}"
                    class="nav-link {{ request()->routeIs('nik-job*') && !request()->routeIs('nik-job.null-relationship') ? 'active' : 'text-white' }}">
                    <i class="bi bi-link-45deg"></i> User ID NIK - Job Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('user-generic-job-role.index') }}"
                    class="nav-link {{ request()->routeIs('user-generic-job-role.*') && !request()->routeIs('user-generic-job-role.null-relationship') ? 'active' : 'text-white' }}">
                    <i class="bi bi-link-45deg"></i> User ID Generic - Job Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('nik-job.null-relationship') }}"
                    class="nav-link {{ request()->routeIs('nik-job.null-relationship') ? 'active' : 'text-white' }}">
                    <i class="bi bi-exclamation-circle"></i> User ID NIK Non Job
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('user-generic-job-role.null-relationship') }}"
                    class="nav-link {{ request()->routeIs('user-generic-job-role.null-relationship') ? 'active' : 'text-white' }}">
                    <i class="bi bi-exclamation-circle"></i> User ID Generic Non Job
                </a>
            </li>
            {{-- @can('Super Admin')
                <hr width="80%" class="my-1" style="margin-left: auto">
                <div class="mx-3 text-white text-end"><strong>Middle DB</strong></div>

                <li class="nav-item">
                    <a href="{{ route('mapping.middle_db.user_generic_uam') }}"
                        class="mb-1 nav-link {{ request()->routeIs('mapping.middle_db.user_generic_uam') ? 'active' : 'text-white' }}">
                        <i class="bi bi-database-fill-gear me-2"></i>Mapping Generic - UAM
                    </a>
                </li>
            @endcan --}}
        </div>
    </div>
