<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('getBreadcrumbs')) {
  function getBreadcrumbs()
  {
    $routeName = Route::currentRouteName();
    $breadcrumbs = [
      'home' => 'Dashboard',

      'companies.index' => 'Master Data > Companies - Index',
      'companies.create' => 'Master Data > Companies - Create',
      'companies.edit' => 'Master Data > Companies - Edit',

      'kompartemens.index' => 'Master Data > Kompartemen - Index',
      'kompartemens.create' => 'Master Data > Kompartemen - Create',
      'kompartemens.edit' => 'Master Data > Kompartemen - Edit',

      'departemens.index' => 'Master Data > Departemen - Index',
      'departemens.create' => 'Master Data > Departemen - Create',
      'departemens.edit' => 'Master Data > Departemen - Edit',

      'job-roles.index' => 'Master Data > Job Roles - Index',
      'job-roles.create' => 'Master Data > Job Roles - Create',
      'job-roles.edit' => 'Master Data > Job Roles - Edit',

      'composite-roles.index' => 'Master Data > Composite Roles - Index',
      'composite-roles.create' => 'Master Data > Composite Roles - Create',
      'composite-roles.edit' => 'Master Data > Composite Roles - Edit',

      'single-roles.index' => 'Master Data > Single Roles - Index',

      'tcodes.index' => 'Master Data > Tcodes',

      'company_kompartemen.upload' => 'Import Data > Company & Job Roles > Upload',

      'cost-center.index' => 'User & Cost Center > Cost Center',
      'cost-center.create' => 'User & Cost Center > Cost Center > Create',
      'cost-center.edit' => 'User & Cost Center > Cost Center > Edit',

      'user-nik.index' => 'User & Cost Center > User NIK',

      'user-generic.index' => 'User & Cost Center > User Generic',
      'user-generic.dashboard' => 'User & Cost Center > Cost Center > Dashboard',

      'access-matrix' => 'Access Matrix',
      'admin.dashboard' => 'Admin Page',
      'users.index' => 'Manage Users',
    ];

    return isset($breadcrumbs[$routeName]) ? '<li class="breadcrumb-item">' . $breadcrumbs[$routeName] . '</li>' : '<li class="breadcrumb-item">Dashboard</li>';
  }
}
