<li class="nav-item">
    <a href="{{ route('checkpoints.index') }}"
        class="mb-1 nav-link {{ request()->routeIs('checkpoints.index') ? 'active' : 'text-white' }}">
        <i class="bi bi-flag-fill me-2"></i> Checkpoints
    </a>
</li>


@can('data.view')
    @include('layouts.sidebar_menu.master_data')

    <hr>

    <div>
        <h5>PROSES</h5>
    </div>

    @include('layouts.sidebar_menu.usmm')

    <hr>

    <div>
        <h5>Report</h5>
    </div>

    @include('layouts.sidebar_menu.report')
@endcan

@can('Super Admin')
    @include('layouts.sidebar_menu.middle_db')
@endcan


@can('manage company info')
    <hr>
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
