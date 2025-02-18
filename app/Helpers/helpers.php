<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('getBreadcrumbs')) {
  function getBreadcrumbs()
  {
    $routeName = Route::currentRouteName();
    $breadcrumbs = [
      'home' => 'Dashboard',
      'companies.index' => 'Master Data > Companies',
      'kompartemens.index' => 'Master Data > Kompartemen',
      'departemens.index' => 'Master Data > Departemen',
      'job-roles.index' => 'Master Data > Job Roles',

      'company_kompartemen.upload' => 'Import Data > Company & Job Roles > Upload',
      'cost-center.index' => 'User & Cost Center > Cost Center',
      'cost-center.create' => 'User & Cost Center > Cost Center > Create',
      'cost-center.edit' => 'User & Cost Center > Cost Center > Edit',

      'access-matrix' => 'Access Matrix',
      'admin.dashboard' => 'Admin Page',
      'users.index' => 'Manage Users',
    ];

    return isset($breadcrumbs[$routeName]) ? '<li class="breadcrumb-item">' . $breadcrumbs[$routeName] . '</li>' : '<li class="breadcrumb-item">Dashboard</li>';
  }
}
