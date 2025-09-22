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
        @php
            $username = Auth::user()->username ?? null;
            $user_company = Auth::user()->loginDetail->company_code ?? null;
        @endphp
        @if ($username == 'superadmin')
            @include('layouts.sidebar_sa')
        @elseif ($user_company == 'A000')
            @include('layouts.sidebar_pi')
        @else
            @include('layouts.sidebar_anper')
        @endif

        {{-- Dynamic spacer: pushes the bottom of the list up only when it overflows --}}
        <li class="sidebar-spacer" aria-hidden="true"></li>
    </ul>
</div>
