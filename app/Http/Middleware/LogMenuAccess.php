<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only log GET requests to avoid duplicates on form submissions
        // Only log authenticated users
        if ($request->isMethod('get') && auth()->check()) {
            $routeName = $request->route()->getName();

            // Skip logging for API data endpoints and AJAX requests
            if ($routeName && !$this->shouldSkipLogging($routeName)) {
                log_audit_trail(
                    'menu_access',
                    null,  // no specific model
                    null,  // no model ID
                    null,  // no before data
                    [
                        'menu' => $this->getMenuName($request),
                        'route_name' => $routeName,
                        'url' => $request->fullUrl(),
                        'referer' => $request->header('referer')
                    ]
                );
            }
        }

        return $next($request);
    }

    /**
     * Determine if this route should skip logging.
     * Skip data/API endpoints that are called frequently via AJAX.
     *
     * @param string $routeName
     * @return bool
     */
    private function shouldSkipLogging(string $routeName): bool
    {
        $skipPatterns = [
            '*.data',
            '*.getData',
            '*.preview_data',
            '*.preview-data',
            'audit-trails.data',
            '*.jsonIndex',
            '*.datatable',
        ];

        foreach ($skipPatterns as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map route names to friendly menu names.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    private function getMenuName(Request $request): string
    {
        $routeName = $request->route()->getName();

        // Menu mapping based on route names from web.php
        $menuMap = [
            // Dashboard
            'home' => 'Dashboard',

            // Audit Trails
            'audit-trails.*' => 'Audit Trail',

            // Profile
            'profile.*' => 'User Profile',

            // Master Data - Company Structure
            'companies.*' => 'Master Data > Company',
            'kompartemens.*' => 'Master Data > Kompartemen',
            'departemens.*' => 'Master Data > Departemen',

            // Master Data - Roles
            'job-roles.*' => 'Master Data > Job Role',
            'composite-roles.*' => 'Master Data > Composite Role',
            'single-roles.*' => 'Master Data > Single Role',
            'tcodes.*' => 'Master Data > Tcode',

            // Relationships
            'job-composite.*' => 'Relationship > Job-Composite',
            'composite-single.*' => 'Relationship > Composite-Single',
            'composite_ao.*' => 'Relationship > Composite-AO',
            'single-tcode.*' => 'Relationship > Single-Tcode',
            'nik-job.*' => 'Relationship > NIK-Job Role',
            'user-generic-job-role.*' => 'Relationship > User Generic-Job Role',
            'relationship.uam.*' => 'Relationship > UAM',

            // Imports - Company & Roles
            'company_kompartemen.*' => 'Import > Company-Kompartemen',
            'composite_single.*' => 'Import > Composite-Single Role',
            'tcode_single_role.*' => 'Import > Tcode-Single Role',
            'unit-kerja.*' => 'Import > Unit Kerja',

            // User Management
            'periode.*' => 'User Management > Periode',
            'user-detail.*' => 'User Management > User Detail',
            'user-nik.*' => 'User Management > User NIK',
            'user-generic.*' => 'User Management > User Generic',
            'user-generic-unit-kerja.*' => 'User Management > User Generic Unit Kerja',
            'user-system.*' => 'User Management > User System',
            'terminated-employee.*' => 'User Management > Terminated Employee',
            'cost-center.*' => 'User Management > Cost Center',
            'prev-user.*' => 'User Management > Previous User',
            'karyawan_unit_kerja.*' => 'User Management > Karyawan Unit Kerja',
            'unit_kerja.*' => 'User Management > Unit Kerja',

            // Dynamic Upload
            'dynamic_upload.*' => 'Import > Dynamic Upload',
            'ussm-job-role.*' => 'Import > USSM Job Role',

            // Middle DB
            'middle_db.*' => 'Middle DB',
            'import.*' => 'Import > Middle DB',

            // Compare
            'compare.*' => 'Data Comparison',

            // Reports
            'report.unit.*' => 'Report > Work Unit',
            'report.empty-job-role.*' => 'Report > Empty Job Role',
            'report.uar.*' => 'Report > UAR',
            'report.uam.*' => 'Report > UAM',
            'report.ba_penarikan.*' => 'Report > BA Penarikan Data',
            'report.anomali.*' => 'Report > Anomali Data',
            'report.master_data.*' => 'Report > Master Data Export',

            // Admin
            'admin.*' => 'Admin',
            'checkpoints.*' => 'Admin > Progress Checkpoint',
            'penomoran-uar.*' => 'Admin > Penomoran UAR',
            'penomoran-uam.*' => 'Admin > Penomoran UAM',
        ];

        // Find matching pattern
        foreach ($menuMap as $pattern => $menuName) {
            if (fnmatch($pattern, $routeName)) {
                return $menuName;
            }
        }

        // Fallback to route name if no match found
        return $routeName ?? 'Unknown Menu';
    }
}
