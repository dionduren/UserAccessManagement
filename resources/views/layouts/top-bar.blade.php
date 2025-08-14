<div class="top-bar d-flex align-items-center">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            {!! getBreadcrumbs() !!}
        </ol>
    </nav>`

    <div class="d-flex align-items-center">
        @auth()
            <!-- Notification Dropdown -->
            <div class="dropdown me-3">

                {{-- NOTIFICATIONS  --}}
                {{-- <button class="btn position-relative" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-item notification-card">Test Notification 1</li>
                    <li class="divider"></li>
                    <li class="dropdown-item notification-card">Test Notification 2</li>
                    <li class="divider"></li>
                    <li class="dropdown-item notification-card">Test Notification 3</li> --}}

                {{-- <div class='cursor-pointer relative flex items-center mt-5'>
                    <div class="image-fit relative mr-1 h-12 w-12 flex-none">
                        <img class="rounded-full"
                            src="{{ Auth::user()->profile_photo_url ?? 'https://randomuser.me/api/portraits/men/64.jpg' }}"
                            alt="Notification Profile Picture - Optional" />
                        <div
                            class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-success dark:border-darkmode-600">
                        </div>
                    </div>
                    <div class="ml-2 overflow-hidden">
                        <div class="flex items-center">
                            <a class="mr-5 truncate font-medium" href="">
                                Name Test
                            </a>
                            <div class="ml-auto whitespace-nowrap text-xs text-slate-400">
                                13:30 WIB
                            </div>
                        </div>
                        <div class="mt-0.5 w-full truncate text-slate-500">
                            <p>Pengajuan Permintaan User Access Baru</p>
                        </div>
                    </div>
                </div> --}}
                </ul>
            </div>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <button class="btn image-fit w-32 h-32 d-flex align-items-center" data-bs-toggle="dropdown">
                    @php
                        if (!function_exists('company_logo_url')) {
                            function company_logo_url(): string
                            {
                                $code = strtoupper((string) data_get(Auth::user(), 'loginDetail.company_code', ''));
                                switch ($code) {
                                    case 'A000':
                                        $filename = 'icon-a000.png';
                                        break;
                                    case 'B000':
                                        $filename = 'icon-b000.png';
                                        break;
                                    case 'D000':
                                        $filename = 'icon-d000.png';
                                        break;
                                    default:
                                        $filename = 'icon-danantara.png';
                                        break;
                                }
                                return asset('images/logo-perusahaan/' . $filename);
                            }
                        }
                    @endphp
                    <img src="{{ company_logo_url() }}" alt="Profile" class="profile-pic me-2">
                    {{-- <img src="{{ Auth::user()->profile_photo_url ?? 'https://xsgames.co/randomusers/assets/avatars/male/49.jpg' }}"
                        class="profile-pic me-2"> --}}
                    {{-- <img src="{{ Auth::user()->profile_photo_url ?? 'https://randomuser.me/api/portraits/men/64.jpg' }}"
                        class="profile-pic me-2"> --}}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <strong>{{ Auth::user()->name }}</strong>
                        <br>
                        <small class="text-muted">Frontend Engineer</small>
                    </li>
                    <li>
                        <a href="{{ route('profile.show') }}" class="dropdown-item">
                            <i class="bi bi-person-circle me-2"></i> Profile
                        </a>
                    </li>
                    {{-- <li><a class="dropdown-item" href="#"><i class="bi bi-person-plus"></i> Add
                            Account</a>
                    </li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-question-circle"></i>
                            Help</a>
                    </li> --}}
                    <li class="divider"></li>
                    <li><a class="dropdown-item text-danger" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a></li>
                </ul>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary w-100">Login</a>
        @endauth
    </div>
</div>

<hr style="margin-bottom: 10px; margin-top: 5px ; padding-top: 0; padding: 0;">


<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>
