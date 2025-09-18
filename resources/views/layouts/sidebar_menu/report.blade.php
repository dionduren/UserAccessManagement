 <div class="mx-3 text-white text-end"><strong>Middle DB</strong></div>
 <li class="nav-item">
     <a href="{{ route('middle_db.raw.uam_relationship.index') }}"
         class="mb-1 nav-link {{ request()->routeIs('middle_db.raw.uam_relationship.*') ? 'active' : 'text-white' }}">
         <i class="bi bi-database-fill-gear me-2"></i>User - UAM Relationship
     </a>
 </li>

 <hr width="80%" class="my-1" style="margin-left: auto">
 <div class="mx-3 text-white text-end"><strong>Local Data</strong></div>
 <li>
     <a href="{{ route('relationship.uam.index') }}"
         class="nav-link {{ request()->routeIs('relationship.uam*') ? 'active' : 'text-white' }}">
         <i class="bi bi-link-45deg"></i> UAM Relationship
     </a>
 </li>

 <hr width="80%" class="my-1" style="margin-left: auto">
 <li class="nav-item">
     <a href="{{ route('report.uar.index') }}"
         class="mb-1 nav-link {{ request()->routeIs('report.uar.index') ? 'active' : 'text-white' }}">
         <i class="bi bi-file-earmark-text me-2"></i> Report UAR
     </a>
 </li>
 <li class="nav-item">
     <a href="{{ route('report.uam.index') }}"
         class="mb-1 nav-link {{ request()->routeIs('report.uam.index') ? 'active' : 'text-white' }}">
         <i class="bi bi-file-earmark-text me-2"></i> Report UAM
     </a>
 </li>
 <li class="nav-item">
     <a href="{{ route('report.ba_penarikan.index') }}"
         class="mb-1 nav-link {{ request()->routeIs('report.ba_penarikan.index') ? 'active' : 'text-white' }}">
         <i class="bi bi-file-earmark-text me-2"></i> Report BA Penarikan Data
     </a>
 </li>
