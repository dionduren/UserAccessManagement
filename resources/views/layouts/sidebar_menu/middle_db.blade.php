<hr>
<div>
    <h5>MIDDLE DB</h5>
</div>


<li class="nav-item">
    <a href="{{ route('middle_db.uam.composite_role.compare') }}"
        class="mb-1 nav-link {{ request()->routeIs('middle_db.uam.composite_role.compare') ? 'active' : 'text-white' }}">
        <i class="bi bi-file-diff me-2"></i> Compare UAM Full Relationship
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('middle_db.master_data_karyawan.duplicates.index') }}"
        class="mb-1 nav-link {{ request()->routeIs('middle_db.master_data_karyawan.duplicates.index') ? 'active' : 'text-white' }}">
        <i class="bi bi-database-fill-gear me-2"></i>Manage Nama Karyawan Duplikat
    </a>
</li>
<hr width="80%" class="my-auto" style="margin-left: auto">

{{-- <div class="dropdown">
                    @php
                        $isMasterDataKaryawanActive = request()->routeIs('middle_db.master_data_karyawan.*');
                    @endphp
                    <a class="mb-1 nav-link dropdown-toggle {{ $isMasterDataKaryawanActive ? 'active' : 'text-white' }}"
data-bs-toggle="dropdown" href="#" role="button"
aria-expanded="{{ $isMasterDataKaryawanActive ? 'true' : 'false' }}">
<i class="bi bi-folder-fill me-2"></i> Master Data Karyawan
</a>
<div class="dropdown-content {{ $isMasterDataKaryawanActive ? 'show' : '' }}">

</div>
</div> --}}
{{-- <li class="nav-item">
                <a href="{{ route('middle_db.unit_kerja.index') }}"
class="mb-1 nav-link {{ request()->routeIs('middle_db.unit_kerja.*') ? 'active' : 'text-white' }}">
<i class="bi bi-diagram-3 me-2"></i> Unit Kerja
</a>
</li> --}}

<hr width="80%" class="my-auto" style="margin-left: auto">

<div class="dropdown mt-2">
    <a class="mb-1 nav-link dropdown-toggle {{ request()->routeIs('middle_db.usmm.*') ? 'active' : 'text-white' }}"
        data-bs-toggle="dropdown" href="#" role="button"
        aria-expanded="{{ request()->routeIs('middle_db.usmm.*') ? 'true' : 'false' }}">
        <i class="bi bi-folder-fill me-2"></i> Master USMM
    </a>
    <div class="dropdown-content {{ request()->routeIs('middle_db.usmm.*') ? 'show' : '' }}">
        <li class="nav-item">
            <a href="{{ route('middle_db.usmm.index') }}"
                class="mb-1 nav-link {{ request()->routeIs('middle_db.usmm.index') ? 'active' : 'text-white' }}">
                <i class="bi bi-person-badge me-2"></i> Active - All USMM
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('middle_db.usmm.activeNIK') }}"
                class="mb-1 nav-link {{ request()->routeIs('middle_db.usmm.activeNIK') ? 'active' : 'text-white' }}">
                <i class="bi bi-person-dash me-2"></i> Active - NIK
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('middle_db.usmm.activeGeneric') }}"
                class="mb-1 nav-link {{ request()->routeIs('middle_db.usmm.activeGeneric') ? 'active' : 'text-white' }}">
                <i class="bi bi-person-dash me-2"></i> Active - Generic
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

{{-- <li class="nav-item">
                <a href="{{ route('middle_db.generic_karyawan_mapping.index') }}"
class="mb-1 nav-link {{ request()->routeIs('middle_db.generic_karyawan_mapping.*') ? 'active' : 'text-white' }}">
<i class="bi bi-people-fill me-2"></i>Generic Karyawan Mapping
</a>
</li> --}}

<div class="dropdown">
    @php
        $isRawUSMMSectionActive = request()->routeIs('middle_db.raw.generic_karyawan_mapping*');
    @endphp
    <a class="mb-1 nav-link dropdown-toggle {{ $isRawUSMMSectionActive ? 'active' : 'text-white' }}"
        data-bs-toggle="dropdown" href="#" role="button"
        aria-expanded="{{ $isRawUSMMSectionActive ? 'true' : 'false' }}">
        <i class="bi bi-folder-fill me-2"></i>RAW USMM Generic
    </a>
    <div class="dropdown-content {{ $isRawUSMMSectionActive ? 'show' : '' }}">

        <li class="nav-item">
            <a href="{{ route('middle_db.raw.generic_karyawan_mapping.index') }}"
                class="mb-1 nav-link {{ request()->routeIs('middle_db.raw.generic_karyawan_mapping.*') ? 'active' : 'text-white' }}">
                <i class="bi bi-people-fill me-2"></i> Generic Karyawan Mapping Raw
            </a>
        </li>
    </div>
</div>

{{-- <hr width="80%" class="my-auto" style="margin-left: auto">

            <div class="dropdown">
                @php
                    $isMasterUAMActive = request()->routeIs(
                        'middle_db.uam.composite_role.*',
                        'middle_db.uam.single_role.*',
                        'middle_db.uam.tcode.*',
                    );
                @endphp
                <a class="mb-1 nav-link dropdown-toggle {{ $isMasterUAMActive ? 'active' : 'text-white' }}"
data-bs-toggle="dropdown" href="#" role="button"
aria-expanded="{{ $isMasterUAMActive ? 'true' : 'false' }}">
<i class="bi bi-folder-fill me-2"></i> Master UAM
</a>
<div class="dropdown-content {{ $isMasterUAMActive ? 'show' : '' }}">
    <li class="nav-item">
        <a href="{{ route('middle_db.uam.composite_role.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.uam.composite_role.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-people-fill me-2"></i> Composite Role
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('middle_db.uam.single_role.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.uam.single_role.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-person-fill me-2"></i> Single Role
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('middle_db.uam.tcode.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.uam.tcode.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-code-slash me-2"></i> Tcode
        </a>
    </li>
</div>
</div> --}}

{{-- <div class="dropdown mt-2">
                @php
                    $isRawSectionActive = request()->routeIs('middle_db.view.uam*');
                @endphp
                <a class="mb-1 nav-link dropdown-toggle {{ $isRawSectionActive ? 'active' : 'text-white' }}"
data-bs-toggle="dropdown" href="#" role="button"
aria-expanded="{{ $isRawSectionActive ? 'true' : 'false' }}">
<i class="bi bi-database-fill-gear me-2"></i> RAW UAM
</a>
<div class="dropdown-content {{ $isRawSectionActive ? 'show' : '' }}">
    <li class="nav-item">
        <a href="{{ route('middle_db.raw.uam_relationship.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.raw.uam_relationship.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-diagram-3 me-2"></i> UAM Full Raw
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('middle_db.view.uam.composite_master.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.composite_master.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-people-fill me-2"></i>RAW DISTINCT - Composite Role
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('middle_db.view.uam.single_master.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.single_master.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-person-fill me-2"></i>RAW DISTINCT - Single Role
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('middle_db.view.uam.tcode_master.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.tcode_master.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-code-slash me-2"></i>RAW DISTINCT - Tcode
        </a>
    </li>
</div>
</div> --}}

{{-- <div class="dropdown">
                @php
                    $isUAMRelationshipActive = request()->routeIs(
                        'middle_db.view.uam.user_composite.*',
                        'middle_db.view.uam.composite_single.*',
                        'middle_db.view.uam.single_tcode.*',
                        'middle_db.view.uam.composite_ao.*',
                    );
                @endphp
                <a class="mb-1 nav-link dropdown-toggle {{ $isUAMRelationshipActive ? 'active' : 'text-white' }}"
data-bs-toggle="dropdown" href="#" role="button"
aria-expanded="{{ $isUAMRelationshipActive ? 'true' : 'false' }}">
<i class="bi bi-database-fill-gear me-2"></i>RAW Relationship UAM
</a>
<div class="dropdown-content {{ $isUAMRelationshipActive ? 'show' : '' }}">
    <li class="nav-item">
        <a href="{{ route('middle_db.view.uam.user_composite.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.user_composite.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-person-badge me-2"></i> User - Composite Role
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('middle_db.view.uam.composite_single.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.composite_single.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-link-45deg me-2"></i> Composite Role - Single Role
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('middle_db.view.uam.single_tcode.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.single_tcode.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-code-slash me-2"></i> Single Role - Tcode
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('middle_db.view.uam.composite_ao.index') }}"
            class="mb-1 nav-link {{ request()->routeIs('middle_db.view.uam.composite_ao.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-diagram-3 me-2"></i> Composite Role - AO
        </a>
    </li>
</div>
</div> --}}
