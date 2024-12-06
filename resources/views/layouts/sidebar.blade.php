<div class="sidebar-heading mt-2" style="padding-left: 10px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4"><i class="bi bi-h-square"></i> Hak Akses</span>
    </a>
</div>
<hr>
<div class="list-group list-group-flush overflow-auto h-100 no-scrollbar" style="padding-left: 10px;">
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : 'text-white' }}">
                <i class="bi bi-house-door me-2"></i> Home
            </a>
        </li>


        <!-- Other Sidebar Links -->
        @can('manage company info')
            <hr style="margin-right: 10px;">
            <li class="dropdown">
                <a href="javascript:void(0)" class="nav-link text-white dropdown-toggle">
                    <i class="bi bi-building me-2"></i> MASTER DATA COMPANY
                </a>
                <ul class="dropdown-content">
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
                        <a href="{{ route('job-roles.index') }}"
                            class="nav-link {{ request()->routeIs('job-roles.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-badge me-2"></i> Job Roles
                        </a>
                    </li>
                </ul>
            </li>


            {{-- <hr>

            MASTER DATA COMPANY

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
                <a href="{{ route('job-roles.index') }}"
                    class="nav-link {{ request()->routeIs('job-roles.index') ? 'active' : 'text-white' }}">
                    <i class="bi bi-person-badge me-2"></i> Job Roles
                </a>
            </li> --}}
        @endcan


        <!-- Additional Items... -->

        @can('manage roles')
            <hr style="margin-right: 10px;">
            <!-- MASTER DATA USER ACCESS -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                    aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i> MASTER DATA USER ACCESS
                </a>
                <div
                    class="dropdown-content {{ request()->is('composite-roles*', 'single-roles*', 'tcodes*') ? 'show' : '' }}">
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

            {{-- <hr>
            MASTER DATA USER ACCESS
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
            </li> --}}


            <hr style="margin-right: 10px;">
            <!-- MASTER DATA RELATIONSHIP -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                    aria-expanded="false">
                    <i class="bi bi-folder-fill me-2"></i> MASTER DATA RELATIONSHIP
                </a>
                <div
                    class="dropdown-content {{ request()->is('job-composite*', 'rel-composite-single*', 'rel-single-tcodes*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('job-composite.index') }}"
                            class="nav-link {{ request()->routeIs('job-composite.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-link-45deg"></i> Job Role - Composite
                        </a>
                    </li>
                    <li>
                        <a href=""
                            class="nav-link {{ request()->routeIs('rel-composite-single.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-link-45deg"></i> Composite - Single Role
                        </a>
                    </li>
                    <li>
                        <a href=""
                            class="nav-link {{ request()->routeIs('rel-single-tcodes.index') ? 'active' : 'text-white' }}">
                            <i class="bi bi-link-45deg"></i> Single Role - Tcodes
                        </a>
                    </li>
                </div>
            </div>

            <?php /* 
                <hr>

                MASTER DATA RELATIONSHIP

                <li>
                    <a href="{{ route('job-composite.index') }}"
                        class="nav-link {{ request()->routeIs('job-composite.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-link-45deg"></i> Job Role - Composite
                    </a>
                </li>

                <li>
                    <a href="" {{-- href="{{ route('rel-composite-single.index') }}" --}}
                        class="nav-link {{ request()->routeIs('rel-composite-single.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-link-45deg"></i> Composite - Single Role
                    </a>
                </li>

                <li>
                    <a href="" {{-- href="{{ route('rel-single-tcodes.index') }}" --}}
                        class="nav-link {{ request()->routeIs('rel-single-tcodes.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-link-45deg"></i> Single Role - Tcodes
                    </a>
                </li>
            */
            ?>


            <hr style="margin-right: 10px;">

            <!-- IMPORT DATA -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle {{ request()->is('company_kompartemen*', 'composite_single*', 'tcode_single_role*') ? 'active' : 'text-white' }}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="bi bi-file-earmark-spreadsheet"></i> IMPORT DATA
                </a>
                <div
                    class="dropdown-content {{ request()->is('company_kompartemen*', 'composite_single*', 'tcode_single_role*') ? 'show' : '' }}">
                    <li class="nav-item">
                        <a href="{{ route('company_kompartemen.upload') }}"
                            class="nav-link {{ request()->routeIs('company_kompartemen.upload') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Job Role - Composite Role
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('composite_single.upload') }}"
                            class="nav-link {{ request()->routeIs('composite_single.upload') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Composite - Single Role
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tcode_single_role.upload') }}"
                            class="nav-link {{ request()->routeIs('tcode_single_role.upload') ? 'active' : 'text-white' }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Single Role - Tcode
                        </a>
                    </li>
                </div>
            </div>

            {{-- <hr>
            IMPORT DATA

            <li class="nav-item">
                <a href="{{ route('company_kompartemen.upload') }}"
                    class="nav-link {{ request()->routeIs('company_kompartemen.upload') ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Job Role - Composite Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('composite_single.upload') }}"
                    class="nav-link {{ request()->routeIs('composite_single.upload') ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Composite - Single Role
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tcode_single_role.upload') }}"
                    class="nav-link {{ request()->routeIs('tcode_single_role.upload') ? 'active' : 'text-white' }}">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Single Role - Tcode
                </a>
            </li> --}}
        @endcan

        @can('manage access-matrix')
            <hr style="margin-right: 10px;">
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

<hr>

<!-- Profile Section -->
<div class="dropdown" style="margin-left: 10px;margin-right:10px">
    @auth
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
            id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ Auth::user()->profile_photo_url ?? 'https://via.placeholder.com/32' }}" alt=""
                width="32" height="32" class="rounded-circle me-2">
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
</div>
