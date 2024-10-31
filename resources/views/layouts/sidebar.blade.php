<a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
    <span class="fs-4"><i class="bi bi-h-square"></i> Hak Akses</span>
</a>
<hr>
<ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : 'text-white' }}">
            <i class="bi bi-house-door me-2"></i> Home
        </a>
    </li>


    <!-- Other Sidebar Links -->
    @can('manage company info')
        <hr>

        <li>
            <a href="{{ route('companies.index') }}"
                class="nav-link {{ request()->routeIs('companies.index') ? 'active' : 'text-white' }}">
                <i class="bi bi-building me-2"></i> Company
            </a>
        </li>

        <li>
            <a href="{{ route('kompartemen.index') }}"
                class="nav-link {{ request()->routeIs('kompartemen.index') ? 'active' : 'text-white' }}">
                <i class="bi bi-diagram-3 me-2"></i> Kompartemen
            </a>
        </li>

        <li>
            <a href="{{ route('departemen.index') }}"
                class="nav-link {{ request()->routeIs('departemen.index') ? 'active' : 'text-white' }}">
                <i class="bi bi-layers me-2"></i> Departemen
            </a>
        </li>
    @endcan

    <hr>

    <!-- Additional Items... -->

    @can('manage roles')
        <li>
            <a href="{{ route('job-roles.index') }}"
                class="nav-link {{ request()->routeIs('job-roles.index') ? 'active' : 'text-white' }}">
                <i class="bi bi-person-badge me-2"></i> Job Roles
            </a>
        </li>
    @endcan

    <hr>

    @can('manage access-matrix')
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

<hr>

<!-- Profile Section -->
<div class="dropdown">
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
