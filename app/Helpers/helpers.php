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

      'job-composite.index' => 'Relationship > Job Roles - Composite Roles Index',

      'company_kompartemen.upload' => 'Import Data > Company & Job Roles > Upload',
      'company_kompartemen.preview_data' => 'Import Data > Company & Job Roles > Preview',

      'composite_single.upload' => 'Import Data > Composite & Single Roles > Upload',
      'composite_single.preview_data' => 'Import Data > Composite & Single Roles > Preview',

      'tcode_single_role.upload' => 'Import Data > Single Roles & Tcode > Upload',
      'tcode_single_role.preview_data' => 'Import Data > Single Roles & Tcode > Preview',

      'cost-center.index' => 'User & Cost Center > Cost Center',
      'cost-center.create' => 'User & Cost Center > Cost Center > Create',
      'cost-center.edit' => 'User & Cost Center > Cost Center > Edit',

      'user-nik.index' => 'User & Cost Center > User NIK',
      'user-detail.index' => 'User & Cost Center > Detail User NIK',

      'user-generic.index' => 'User & Cost Center > User Generic',
      'user-generic.dashboard' => 'User & Cost Center > Cost Center > Dashboard',

      // PERIODE

      'periode.index' => 'User & Cost Center > Periode - Index',
      'periode.create' => 'User & Cost Center > Periode - Create',
      'periode.edit' => 'User & Cost Center > Periode - Edit',

      // PROFILE

      'profile.show' => 'User Profile',

      // ADMIN

      // 'access-matrix' => 'Access Matrix',
      // 'admin.dashboard' => 'Admin Page',

      'penomoran-uar.index' => 'Admin Page > Penomoran UAR - Index',
      'penomoran-uar.create' => 'Admin Page > Penomoran UAR - Create',
      'penomoran-uar.edit' => 'Admin Page > Penomoran UAR - Edit',

      'penomoran-uam.index' => 'Admin Page > Penomoran UAM - Index',
      'penomoran-uam.create' => 'Admin Page > Penomoran UAM - Create',
      'penomoran-uam.edit' => 'Admin Page > Penomoran UAM - Edit',

      'admin.access-matrix.roles.index' => 'Admin Page > Access Matrix > Role Management',
      // 'admin.access-matrix.role.create' => 'Admin Page > Access Matrix > Role Management > Create',
      // 'admin.access-matrix.role.edit' => 'Admin Page > Access Matrix > Role Management > Edit',

      'admin.access-matrix.permissions.index' => 'Admin Page > Access Matrix > Permission Management',
      'admin.access-matrix.permissions.create' => 'Admin Page > Access Matrix > Permission Management > Create',
      'admin.access-matrix.permissions.edit' => 'Admin Page > Access Matrix > Permission Management > Edit',

      'admin.email-change-requests.index' => 'Admin Page > Email Change Request',

      'admin.users.index' => 'Admin Page > Manage Users',
      'admin.users.create' => 'Admin Page > Manage Users > Create',
      'admin.users.edit' => 'Admin Page > Manage Users > Edit',

      // 'job-roles.getData' => 'Master Data > Job Roles - Data',
      // 'unit-kerja.upload-form' => 'Import Data > Unit Kerja - Form',
      // 'unit-kerja.upload' => 'Import Data > Unit Kerja - Upload',

      // 'composite-roles.data' => 'Master Data > Composite Roles - Data',
      // 'single-roles.data' => 'Master Data > Single Roles - Data',
      // 'tcodes.data' => 'Master Data > Tcodes - Data',

      // 'job-composite.data' => 'Relationship > Job Composite - Data',
      // 'job-composite.empty-composite' => 'Relationship > Job Composite - Empty Composite',
      // 'job-composite.company-composite' => 'Relationship > Job Composite - Company Composite',

      // 'composite-single.jsonIndex' => 'Relationship > Composite-Single - Data Set',
      // 'composite-single.filter-company' => 'Relationship > Composite-Single - Filter Company',

      // 'single-tcode.jsonIndex' => 'Relationship > Single-Tcode - Data Set',
      // 'single-tcode.filter-company' => 'Relationship > Single-Tcode - Filter Company',

      // 'company_kompartemen.upload' => 'Import Data > Company-Kompartemen - Upload',
      // 'company_kompartemen.preview' => 'Import Data > Company-Kompartemen - Preview',
      // 'company_kompartemen.preview_data' => 'Import Data > Company-Kompartemen - Preview Data',
      // 'company_kompartemen.confirm' => 'Import Data > Company-Kompartemen - Confirm',

      // 'composite_single.confirm' => 'Import Data > Composite-Single - Confirm',
      // 'tcode_single_role.confirm' => 'Import Data > Tcode-Single Role - Confirm',

      // 'periode.index' => 'Master Data > Periode - Index',
      // 'periode.create' => 'Master Data > Periode - Create',
      // 'periode.store' => 'Master Data > Periode - Store',
      // 'periode.show' => 'Master Data > Periode - Show',
      // 'periode.edit' => 'Master Data > Periode - Edit',
      // 'periode.update' => 'Master Data > Periode - Update',
      // 'periode.destroy' => 'Master Data > Periode - Destroy',

      // 'user-nik.upload.form' => 'User & Cost Center > User NIK Upload - Form',
      // 'user-nik.upload.store' => 'User & Cost Center > User NIK Upload - Store',
      // 'user-nik.upload.preview' => 'User & Cost Center > User NIK Upload - Preview',
      // 'user-nik.upload.preview_data' => 'User & Cost Center > User NIK Upload - Preview Data',
      // 'user-nik.upload.update-inline-session' => 'User & Cost Center > User NIK Upload - Update Inline',
      // 'user-nik.upload.submitSingle' => 'User & Cost Center > User NIK Upload - Submit Single',
      // 'user-nik.upload.confirm' => 'User & Cost Center > User NIK Upload - Confirm',

      // 'user-nik.index_mixed' => 'User & Cost Center > User NIK - Mixed Index',
      // 'user-nik.check-user-detail' => 'User & Cost Center > User NIK - Check Detail',
      // 'user-nik.download-template' => 'User & Cost Center > User NIK - Download Template',
      // 'user-nik.compare' => 'User & Cost Center > User NIK - Compare',
      // 'user-nik.get-periodic' => 'User & Cost Center > User NIK - Periodic',

      // 'user-detail.getData' => 'User & Cost Center > Detail User NIK - Data',
      // 'terminated-employee.get-data' => 'Master Data > Terminated Employee - Data',

      // 'dashboard.user-generic' => 'User & Cost Center > Cost Center > Dashboard',
      // 'user-generic.compare' => 'User & Cost Center > Generic User - Compare',
      // 'user-generic.get-periodic' => 'User & Cost Center > Generic User - Periodic',

      // 'prev-user.index' => 'User & Cost Center > Previous User - Index',
      // 'prev-user.update' => 'User & Cost Center > Previous User - Update',
      // 'prev-user.edit' => 'User & Cost Center > Previous User - Edit',
      // 'prev-user.full-update' => 'User & Cost Center > Previous User - Full Update',

      // 'nik-job.get-by-periode' => 'Relationship > NIK-Job Role - Periodic',

      // 'dynamic_upload.upload' => 'Dynamic Upload - Upload',
      // 'dynamic_upload.handleUpload' => 'Dynamic Upload - Handle Upload',
      // 'dynamic_upload.preview' => 'Dynamic Upload - Preview',
      // 'dynamic_upload.preview_data' => 'Dynamic Upload - Preview Data',
      // 'dynamic_upload.submitAll' => 'Dynamic Upload - Submit All',

      // 'report.unit' => 'Reports > Work Unit - Index',
      // 'report.unit.groupedData' => 'Reports > Work Unit - Grouped Data',
      // 'report.empty-job-role.index' => 'Reports > Empty Job Role - Index',

      // 'access-matrix.assign-role' => 'Access Matrix > Assign Role',
      // 'access-matrix.assign-permission' => 'Access Matrix > Assign Permission',

      // 'admin.fetch-employee' => 'Admin Page > Fetch Employee',
      // 'json.regenerate' => 'Admin Page > Regenerate JSON',

    ];

    return isset($breadcrumbs[$routeName]) ? '<li class="breadcrumb-item">' . $breadcrumbs[$routeName] . '</li>' : '<li class="breadcrumb-item"></li>';
  }
}

if (!function_exists('clean')) {
  /**
   * Clean HTML using HTMLPurifier
   * Allows safe HTML tags like <b>, <i>, <ul>, <li>, <br>, <span>
   * Removes dangerous tags like <script>, <iframe>, onclick attributes
   */
  function clean($dirty_html)
  {
    if (empty($dirty_html)) {
      return '';
    }

    return \Mews\Purifier\Facades\Purifier::clean($dirty_html, [
      'HTML.Allowed' => 'b,strong,i,em,u,ul,ol,li,br,p,span[style|class],small,div[class],a[href|title|class]',
      'CSS.AllowedProperties' => 'color,font-weight,text-decoration',
      'AutoFormat.RemoveEmpty' => true,
    ]);
  }
}

if (!function_exists('log_audit_trail')) {
  /**
   * Log an audit trail entry.
   *
   * @param string $activityType Type of activity (e.g., create, update, delete, login).
   * @param string|null $modelType The model class name.
   * @param int|null $modelId The model ID.
   * @param array|null $beforeData Data before the action (for update/delete).
   * @param array|null $afterData Data after the action (for create/update).
   * @param int|null $userId User ID performing the activity.
   * @return void
   */
  function log_audit_trail(
    string $activityType,
    ?string $modelType = null,
    ?int $modelId = null,
    ?array $beforeData = null,
    ?array $afterData = null,
    ?int $userId = null
  ) {
    $user = auth()->user();
    $request = request();

    \App\Models\AuditTrail::create([
      'user_id' => $userId ?? $user->id ?? null,
      'username' => $user->username ?? $user->name ?? null,
      'activity_type' => $activityType,
      'model_type' => $modelType,
      'model_id' => $modelId,
      'route' => $request->path(),
      'method' => $request->method(),
      'status_code' => null, // Will be set by middleware if needed
      'session_id' => session()->getId(),
      'request_id' => $request->hasHeader('X-Request-ID') ? $request->header('X-Request-ID') : \Illuminate\Support\Str::uuid(),
      'ip_address' => $request->ip(),
      'user_agent' => $request->header('User-Agent'),
      'before_data' => $beforeData,
      'after_data' => $afterData,
      'logged_at' => now(),
    ]);
  }
}
