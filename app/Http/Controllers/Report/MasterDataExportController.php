<?php

namespace App\Http\Controllers\Report;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\JobRole;
use App\Models\NIKJobRole;
use App\Models\Periode;
use App\Models\SingleRole;
use App\Models\Tcode;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MasterDataExportController extends Controller
{
    // Maximum rows for Excel export (configurable)
    const MAX_EXPORT_ROWS = 50000;

    /**
     * Show the export filter page
     */
    public function index()
    {
        $userCompany = auth()->user()->loginDetail->company_code ?? null;

        // Get companies based on user access
        if ($userCompany && $userCompany !== 'A000') {
            $companies = Company::where('company_code', $userCompany)->get();
        } else {
            $companies = Company::all();
        }

        $periodes = Periode::orderBy('id', 'desc')->get();

        // Pass MAX_EXPORT_ROWS to view
        $maxExportRows = self::MAX_EXPORT_ROWS;

        return view('master-data.role-export.index', compact('companies', 'periodes', 'userCompany', 'maxExportRows'));
    }

    /**
     * Preview data in DataTable (AJAX)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'start_point' => 'required|in:user,job_role,composite_role,single_role,tcode',
            'end_point' => 'required|in:user,job_role,composite_role,single_role,tcode',
        ]);

        $startPoint = $request->start_point;
        $endPoint = $request->end_point;
        $companyId = $request->company_id;
        $periodeId = $request->periode_id;

        // If no periode selected, use latest
        if (!$periodeId) {
            $periodeId = Periode::orderBy('id', 'desc')->first()?->id;
        }

        // Build query based on relationship
        $query = $this->buildQuery($startPoint, $endPoint, $companyId, $periodeId);

        // Get column definitions
        $path = $this->getRelationshipPath($startPoint, $endPoint);
        $columns = $this->getColumnDefinitions($path);

        // Get all records with proper eager loading
        $records = $query->get();

        // Transform data - flatten each relationship row
        $transformedData = [];
        $rowNumber = 1;

        foreach ($records as $record) {
            $rows = $this->transformRecordToRows($record, $path);

            foreach ($rows as $row) {
                $transformedData[] = array_merge(
                    ['row_number' => $rowNumber++],
                    $row
                );
            }
        }

        // Apply search filter if provided
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $transformedData = array_filter($transformedData, function ($row) use ($searchValue) {
                foreach ($row as $key => $value) {
                    if ($key === 'row_number') continue;
                    if (stripos($value, $searchValue) !== false) {
                        return true;
                    }
                }
                return false;
            });

            $transformedData = array_values($transformedData);
        }

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);

        $totalRecords = count($transformedData);
        $paginatedData = array_slice($transformedData, $start, $length);

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $paginatedData,
            'columns' => $columns
        ]);
    }

    /**
     * Export to Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_point' => 'required|in:user,job_role,composite_role,single_role,tcode',
            'end_point' => 'required|in:user,job_role,composite_role,single_role,tcode',
        ]);

        $startPoint = $request->start_point;
        $endPoint = $request->end_point;
        $companyId = $request->company_id;
        $periodeId = $request->periode_id;

        // If no periode selected, use latest
        if (!$periodeId) {
            $periodeId = Periode::orderBy('id', 'desc')->first()?->id;
        }

        // Build query
        $query = $this->buildQuery($startPoint, $endPoint, $companyId, $periodeId);
        $records = $query->get();

        // Get relationship path and columns
        $path = $this->getRelationshipPath($startPoint, $endPoint);
        $columns = $this->getColumnDefinitions($path);

        // Transform data
        $exportData = [];
        $headerRow = array_merge(['No'], array_column($columns, 'title'));
        $exportData[] = $headerRow;

        // Get column names in order
        $columnNames = array_column($columns, 'name');

        $rowNumber = 1;
        $totalRows = 0;

        foreach ($records as $record) {
            $rows = $this->transformRecordToRows($record, $path);

            foreach ($rows as $row) {
                if ($totalRows >= self::MAX_EXPORT_ROWS) {
                    break 2;
                }

                // Extract values in the correct column order
                $orderedValues = [];
                foreach ($columnNames as $colName) {
                    $orderedValues[] = $row[$colName] ?? '-';
                }

                $exportData[] = array_merge([$rowNumber++], $orderedValues);
                $totalRows++;
            }
        }

        // Create metadata sheet
        $metadata = $this->generateMetadata($companyId, $periodeId, $startPoint, $endPoint, $totalRows, $records->count());

        // Generate filename
        $filename = $this->generateFilename($companyId, $startPoint, $endPoint, $periodeId);

        // Create multi-sheet export
        return Excel::download(new ArrayExport($exportData, $metadata), $filename);
    }

    /**
     * Get relationship path between start and end points
     */
    private function getRelationshipPath($start, $end)
    {
        $hierarchy = ['user', 'job_role', 'composite_role', 'single_role', 'tcode'];

        $startIdx = array_search($start, $hierarchy);
        $endIdx = array_search($end, $hierarchy);

        if ($startIdx === false || $endIdx === false) {
            return [];
        }

        // Forward or reverse path
        if ($startIdx <= $endIdx) {
            return array_slice($hierarchy, $startIdx, $endIdx - $startIdx + 1);
        } else {
            return array_reverse(array_slice($hierarchy, $endIdx, $startIdx - $endIdx + 1));
        }
    }

    /**
     * Build query based on start and end points
     */
    private function buildQuery($start, $end, $companyId, $periodeId)
    {
        $path = $this->getRelationshipPath($start, $end);

        switch ($start) {
            case 'user':
                return $this->buildUserQuery($path, $companyId, $periodeId);
            case 'job_role':
                return $this->buildJobRoleQuery($path, $companyId, $periodeId);
            case 'composite_role':
                return $this->buildCompositeRoleQuery($path, $companyId, $periodeId);
            case 'single_role':
                return $this->buildSingleRoleQuery($path, $companyId, $periodeId);
            case 'tcode':
                return $this->buildTcodeQuery($path, $companyId, $periodeId);
            default:
                return collect([]);
        }
    }

    /**
     * Build User-based query
     */
    private function buildUserQuery($path, $companyId, $periodeId)
    {
        $query = NIKJobRole::query()
            ->where('periode_id', $periodeId)
            ->whereNull('deleted_at')
            ->with([
                'UserNIKUnitKerja:id,nik,nama',
                'userGeneric:id,user_code,user_profile',
                'periode:id,definisi',
                'jobRole:id,job_role_id,nama,company_id,kompartemen_id,departemen_id',
                'jobRole.company:company_code,nama',
                'jobRole.kompartemen:kompartemen_id,nama',
                'jobRole.departemen:departemen_id,nama',
            ]);

        // Load composite role if needed
        if (in_array('composite_role', $path)) {
            $query->with([
                'jobRole.compositeRole:id,jabatan_id,nama,deskripsi,company_id',
            ]);
        }

        // Load single roles if needed
        if (in_array('single_role', $path)) {
            $query->with([
                'jobRole.compositeRole.singleRoles:id,nama,deskripsi'
            ]);
        }

        // Load tcodes if needed
        if (in_array('tcode', $path)) {
            $query->with([
                'jobRole.compositeRole.singleRoles.tcodes:id,code,deskripsi'
            ]);
        }

        // Apply company filter
        if ($companyId) {
            $query->whereHas('jobRole', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return $query;
    }

    /**
     * Build JobRole-based query
     */
    private function buildJobRoleQuery($path, $companyId, $periodeId)
    {
        $query = JobRole::query()
            ->whereNull('deleted_at')
            ->with([
                'company:company_code,nama',
                'kompartemen:kompartemen_id,nama',
                'departemen:departemen_id,nama'
            ]);

        // Reverse: Load users
        if (in_array('user', $path) && array_search('user', $path) < array_search('job_role', $path)) {
            $query->with([
                'NIKJobRole' => function ($q) use ($periodeId) {
                    $q->where('periode_id', $periodeId)->whereNull('deleted_at');
                },
                'NIKJobRole.UserNIKUnitKerja:id,nik,nama',
                'NIKJobRole.userGeneric:id,user_code,user_profile',
                'NIKJobRole.periode:id,definisi'
            ]);
        }

        // Forward: Load composites/singles/tcodes
        if (in_array('composite_role', $path)) {
            $query->with([
                'compositeRole:id,jabatan_id,nama,deskripsi,company_id',
            ]);
        }

        if (in_array('single_role', $path)) {
            $query->with(['compositeRole.singleRoles:id,nama,deskripsi']);
        }

        if (in_array('tcode', $path)) {
            $query->with(['compositeRole.singleRoles.tcodes:id,code,deskripsi']);
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    /**
     * Build CompositeRole-based query
     */
    private function buildCompositeRoleQuery($path, $companyId, $periodeId)
    {
        $query = CompositeRole::query()
            ->whereNull('deleted_at')
            ->with(['company:company_code,nama']);

        // Reverse: Load job roles and/or users
        if (in_array('job_role', $path)) {
            $query->with([
                'jobRole:id,job_role_id,nama,company_id,kompartemen_id,departemen_id',
                'jobRole.company:company_code,nama',
                'jobRole.kompartemen:kompartemen_id,nama',
                'jobRole.departemen:departemen_id,nama'
            ]);

            if (in_array('user', $path)) {
                $query->with([
                    'jobRole.NIKJobRole' => function ($q) use ($periodeId) {
                        $q->where('periode_id', $periodeId)->whereNull('deleted_at');
                    },
                    'jobRole.NIKJobRole.UserNIKUnitKerja:id,nik,nama',
                    'jobRole.NIKJobRole.userGeneric:id,user_code,user_profile'
                ]);
            }
        }

        // Forward: Load singles/tcodes
        if (in_array('single_role', $path)) {
            $query->with(['singleRoles:id,nama,deskripsi']);
        }

        if (in_array('tcode', $path)) {
            $query->with(['singleRoles.tcodes:id,code,deskripsi']);
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    /**
     * Build SingleRole-based query
     */
    private function buildSingleRoleQuery($path, $companyId, $periodeId)
    {
        $query = SingleRole::query()->whereNull('deleted_at');

        // Reverse: Load composites/job roles/users
        if (in_array('composite_role', $path)) {
            $query->with([
                'compositeRoles:id,jabatan_id,nama,deskripsi,company_id',
                'compositeRoles.company:company_code,nama'
            ]);

            if (in_array('job_role', $path)) {
                $query->with([
                    'compositeRoles.jobRole:id,job_role_id,nama,company_id,kompartemen_id,departemen_id',
                    'compositeRoles.jobRole.company:company_code,nama',
                    'compositeRoles.jobRole.kompartemen:kompartemen_id,nama',
                    'compositeRoles.jobRole.departemen:departemen_id,nama'
                ]);

                if (in_array('user', $path)) {
                    $query->with([
                        'compositeRoles.jobRole.NIKJobRole' => function ($q) use ($periodeId) {
                            $q->where('periode_id', $periodeId)->whereNull('deleted_at');
                        },
                        'compositeRoles.jobRole.NIKJobRole.UserNIKUnitKerja:id,nik,nama',
                        'compositeRoles.jobRole.NIKJobRole.userGeneric:id,user_code,user_profile'
                    ]);
                }
            }
        }

        // Forward: Load tcodes
        if (in_array('tcode', $path)) {
            $query->with(['tcodes:id,code,deskripsi']);
        }

        // Company filter through composite roles
        if ($companyId) {
            $query->whereHas('compositeRoles', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return $query;
    }

    /**
     * Build Tcode-based query (reverse only)
     */
    private function buildTcodeQuery($path, $companyId, $periodeId)
    {
        $query = Tcode::query()->whereNull('deleted_at');

        // Reverse: Load single roles
        if (in_array('single_role', $path)) {
            $query->with(['singleRoles:id,nama,deskripsi']);

            if (in_array('composite_role', $path)) {
                $query->with([
                    'singleRoles.compositeRoles:id,jabatan_id,nama,deskripsi,company_id',
                    'singleRoles.compositeRoles.company:company_code,nama'
                ]);

                if (in_array('job_role', $path)) {
                    $query->with([
                        'singleRoles.compositeRoles.jobRole:id,job_role_id,nama,company_id,kompartemen_id,departemen_id',
                        'singleRoles.compositeRoles.jobRole.company:company_code,nama',
                        'singleRoles.compositeRoles.jobRole.kompartemen:kompartemen_id,nama',
                        'singleRoles.compositeRoles.jobRole.departemen:departemen_id,nama'
                    ]);

                    if (in_array('user', $path)) {
                        $query->with([
                            'singleRoles.compositeRoles.jobRole.NIKJobRole' => function ($q) use ($periodeId) {
                                $q->where('periode_id', $periodeId)->whereNull('deleted_at');
                            },
                            'singleRoles.compositeRoles.jobRole.NIKJobRole.UserNIKUnitKerja:id,nik,nama',
                            'singleRoles.compositeRoles.jobRole.NIKJobRole.userGeneric:id,user_code,user_profile'
                        ]);
                    }
                }
            }
        }

        // Company filter through composite roles
        if ($companyId) {
            $query->whereHas('singleRoles.compositeRoles', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return $query;
    }

    /**
     * Transform record to multiple rows (handles one-to-many relationships)
     * SIMPLIFIED VERSION - Based on exportUserJobCompositeExcel pattern
     */
    private function transformRecordToRows($record, $path)
    {
        $rows = [];

        // Extract base data depending on starting point
        $baseData = $this->extractBaseData($record, $path);

        // Navigate through relationships and multiply rows
        $this->multiplyRows($record, $path, 0, $baseData, $rows);

        return $rows;
    }

    /**
     * Recursively multiply rows for one-to-many relationships
     */
    private function multiplyRows($record, $path, $depth, $currentData, &$rows)
    {
        if ($depth >= count($path)) {
            $rows[] = $currentData;
            return;
        }

        $entity = $path[$depth];

        // Get the related data for this level
        $related = $this->getRelatedData($record, $entity, $path, $depth);

        if (is_array($related) && !empty($related)) {
            // Multiple related records - create row for each
            foreach ($related as $item) {
                $itemData = $this->extractEntityFields($item, $entity, $path, $depth);
                $this->multiplyRows($item, $path, $depth + 1, array_merge($currentData, $itemData), $rows);
            }
        } elseif (is_object($related)) {
            // Single related record
            $itemData = $this->extractEntityFields($related, $entity, $path, $depth);
            $this->multiplyRows($related, $path, $depth + 1, array_merge($currentData, $itemData), $rows);
        } else {
            // No related data - use empty values
            $emptyData = $this->getEmptyEntityData($entity, $path, $depth);
            $this->multiplyRows($record, $path, $depth + 1, array_merge($currentData, $emptyData), $rows);
        }
    }

    /**
     * Extract base data from starting point
     */
    private function extractBaseData($record, $path)
    {
        switch ($path[0]) {
            case 'user':
                return $this->extractUserFields($record, $path, 0);
            case 'job_role':
                return $this->extractJobRoleFields($record, $path, 0);
            case 'composite_role':
                return $this->extractCompositeFields($record, $path, 0);
            case 'single_role':
                return $this->extractSingleRoleFields($record);
            case 'tcode':
                return $this->extractTcodeFields($record);
            default:
                return [];
        }
    }

    /**
     * Get related data for current entity
     */
    private function getRelatedData($record, $entity, $path, $depth)
    {
        // Skip if this is the starting entity (already extracted)
        if ($depth === 0) {
            return $record;
        }

        $prevEntity = $path[$depth - 1];

        // User → Job Role
        if ($prevEntity === 'user' && $entity === 'job_role') {
            return $record->jobRole;
        }

        // Job Role → User (reverse)
        if ($prevEntity === 'job_role' && $entity === 'user') {
            return $record->NIKJobRole ? $record->NIKJobRole->all() : [];
        }

        // Job Role → Composite Role
        if ($prevEntity === 'job_role' && $entity === 'composite_role') {
            return $record->compositeRole;
        }

        // Composite Role → Job Role (reverse)
        if ($prevEntity === 'composite_role' && $entity === 'job_role') {
            return $record->jobRole;
        }

        // Composite Role → Single Roles
        if ($prevEntity === 'composite_role' && $entity === 'single_role') {
            return $record->singleRoles ? $record->singleRoles->all() : [];
        }

        // Single Role → Composite Roles (reverse)
        if ($prevEntity === 'single_role' && $entity === 'composite_role') {
            return $record->compositeRoles ? $record->compositeRoles->all() : [];
        }

        // Single Role → Tcodes
        if ($prevEntity === 'single_role' && $entity === 'tcode') {
            return $record->tcodes ? $record->tcodes->all() : [];
        }

        // Tcode → Single Roles (reverse)
        if ($prevEntity === 'tcode' && $entity === 'single_role') {
            return $record->singleRoles ? $record->singleRoles->all() : [];
        }

        return null;
    }

    /**
     * Extract entity-specific fields (context-aware)
     */
    private function extractEntityFields($record, $entity, $path, $depth)
    {
        if (!$record) {
            return $this->getEmptyEntityData($entity, $path, $depth);
        }

        switch ($entity) {
            case 'user':
                return $this->extractUserFields($record, $path, $depth);
            case 'job_role':
                return $this->extractJobRoleFields($record, $path, $depth);
            case 'composite_role':
                return $this->extractCompositeFields($record, $path, $depth);
            case 'single_role':
                return $this->extractSingleRoleFields($record);
            case 'tcode':
                return $this->extractTcodeFields($record);
            default:
                return [];
        }
    }

    /**
     * Extract user fields
     */
    private function extractUserFields($record, $path, $depth)
    {
        $user = $record->user_type === 'nik' ? $record->UserNIKUnitKerja : $record->userGeneric;
        $userName = $record->user_type === 'nik'
            ? ($user?->nama ?? '-')
            : ($user?->user_profile ?? '-');

        return [
            'user_code' => $record->nik ?? '-',
            'user_name' => $userName,
            'user_type' => strtoupper($record->user_type ?? '-'),
        ];
    }

    /**
     * Extract job role fields (context-aware)
     */
    private function extractJobRoleFields($record, $path, $depth)
    {
        $fields = [
            'job_role_id' => $record->job_role_id ?? '-',
            'job_role_name' => $record->nama ?? '-',
        ];

        // Only add company/kompartemen/departemen if:
        // 1. User is in the path (use job role's organizational data)
        // 2. OR job role is the starting point
        if (in_array('user', $path) || $depth === 0) {
            $fields['company'] = $record->company?->nama ?? '-';
            $fields['kompartemen'] = $record->kompartemen?->nama ?? '-';
            $fields['departemen'] = $record->departemen?->nama ?? '-';
        }

        return $fields;
    }

    /**
     * Extract composite role fields (context-aware)
     */
    private function extractCompositeFields($record, $path, $depth)
    {
        $fields = [
            'composite_role_name' => $record->nama ?? '-',
            'composite_description' => $record->deskripsi ?? '-',
        ];

        // Only add company if:
        // 1. Job role is NOT in the path (use composite's company)
        // 2. OR composite is the starting point
        if (!in_array('job_role', $path) || $depth === 0) {
            $fields['company'] = $record->company?->nama ?? '-';
        }

        return $fields;
    }

    /**
     * Extract single role fields
     */
    private function extractSingleRoleFields($record)
    {
        return [
            'single_role_description' => $record->deskripsi ?? '-',
        ];
    }

    /**
     * Extract tcode fields
     */
    private function extractTcodeFields($record)
    {
        return [
            'tcode' => $record->code ?? '-',
            'tcode_description' => $record->deskripsi ?? '-',
        ];
    }

    /**
     * Get column definitions based on path (context-aware)
     */
    private function getColumnDefinitions($path)
    {
        $columns = [];

        foreach ($path as $index => $entity) {
            switch ($entity) {
                case 'user':
                    // Add company/kompartemen/departemen before user columns
                    // Only if job role is in the path
                    if (in_array('job_role', $path)) {
                        $columns[] = ['name' => 'company', 'title' => 'Company'];
                        $columns[] = ['name' => 'kompartemen', 'title' => 'Kompartemen'];
                        $columns[] = ['name' => 'departemen', 'title' => 'Departemen'];
                    }
                    $columns[] = ['name' => 'user_code', 'title' => 'User Code'];
                    $columns[] = ['name' => 'user_name', 'title' => 'User Name'];
                    $columns[] = ['name' => 'user_type', 'title' => 'User Type'];
                    break;

                case 'job_role':
                    // Add company/kompartemen/departemen if user is NOT in path
                    // or job role is starting point
                    if (!in_array('user', $path) || $index === 0) {
                        $columns[] = ['name' => 'company', 'title' => 'Company'];
                        $columns[] = ['name' => 'kompartemen', 'title' => 'Kompartemen'];
                        $columns[] = ['name' => 'departemen', 'title' => 'Departemen'];
                    }
                    $columns[] = ['name' => 'job_role_id', 'title' => 'Job Role ID'];
                    $columns[] = ['name' => 'job_role_name', 'title' => 'Job Role Name'];
                    break;

                case 'composite_role':
                    // Add company if job role is NOT in path or composite is starting point
                    if (!in_array('job_role', $path) || $index === 0) {
                        $columns[] = ['name' => 'company', 'title' => 'Company'];
                    }
                    $columns[] = ['name' => 'composite_role_name', 'title' => 'Composite Role Name'];
                    $columns[] = ['name' => 'composite_description', 'title' => 'Composite Description'];
                    break;

                case 'single_role':
                    $columns[] = ['name' => 'single_role_description', 'title' => 'Single Role Description'];
                    break;

                case 'tcode':
                    $columns[] = ['name' => 'tcode', 'title' => 'Tcode'];
                    $columns[] = ['name' => 'tcode_description', 'title' => 'Tcode Description'];
                    break;
            }
        }

        return $columns;
    }

    /**
     * Get empty entity data with "-" (context-aware)
     */
    private function getEmptyEntityData($entity, $path, $depth)
    {
        switch ($entity) {
            case 'user':
                $data = [
                    'user_code' => '-',
                    'user_name' => '-',
                    'user_type' => '-',
                ];
                if (in_array('job_role', $path)) {
                    $data = array_merge([
                        'company' => '-',
                        'kompartemen' => '-',
                        'departemen' => '-',
                    ], $data);
                }
                return $data;

            case 'job_role':
                $data = [
                    'job_role_id' => '-',
                    'job_role_name' => '-',
                ];
                if (!in_array('user', $path) || $depth === 0) {
                    $data = array_merge([
                        'company' => '-',
                        'kompartemen' => '-',
                        'departemen' => '-',
                    ], $data);
                }
                return $data;

            case 'composite_role':
                $data = [
                    'composite_role_name' => '-',
                    'composite_description' => '-',
                ];
                if (!in_array('job_role', $path) || $depth === 0) {
                    $data = array_merge([
                        'company' => '-',
                    ], $data);
                }
                return $data;

            case 'single_role':
                return [
                    'single_role_description' => '-',
                ];

            case 'tcode':
                return [
                    'tcode' => '-',
                    'tcode_description' => '-',
                ];

            default:
                return [];
        }
    }

    /**
     * Generate metadata for second sheet
     */
    private function generateMetadata($companyId, $periodeId, $startPoint, $endPoint, $exportedRows, $totalRecords)
    {
        $company = $companyId ? Company::where('company_code', $companyId)->first() : null;
        $periode = Periode::find($periodeId);
        $path = $this->getRelationshipPath($startPoint, $endPoint);

        $metadata = [];
        $metadata[] = ['Export Information', ''];
        $metadata[] = ['Generated', now()->format('Y-m-d H:i:s')];
        $metadata[] = ['User', auth()->user()->email ?? 'Unknown'];
        $metadata[] = ['Company Filter', $company ? $company->company_code . ' - ' . $company->nama : 'All Companies'];
        $metadata[] = ['Periode', $periode ? $periode->definisi . ' (ID: ' . $periode->id . ')' : 'Latest'];
        $metadata[] = ['Start Point', ucfirst(str_replace('_', ' ', $startPoint))];
        $metadata[] = ['End Point', ucfirst(str_replace('_', ' ', $endPoint))];
        $metadata[] = ['Relationship Path', implode(' → ', array_map(fn($p) => ucfirst(str_replace('_', ' ', $p)), $path))];
        $metadata[] = ['Total Base Records', $totalRecords];
        $metadata[] = ['Exported Rows', $exportedRows];

        if ($exportedRows >= self::MAX_EXPORT_ROWS) {
            $metadata[] = ['Warning', 'Export limited to ' . number_format(self::MAX_EXPORT_ROWS) . ' rows'];
        }

        return $metadata;
    }

    /**
     * Generate filename
     */
    private function generateFilename($companyId, $startPoint, $endPoint, $periodeId)
    {
        $companyCode = $companyId ? Company::where('company_code', $companyId)->value('company_code') : 'AllCompanies';
        $start = ucfirst(str_replace('_', '', $startPoint));
        $end = ucfirst(str_replace('_', '', $endPoint));
        $date = now()->format('Y-m-d');

        $filename = "{$companyCode}_{$start}_to_{$end}";

        if ($periodeId) {
            $periode = Periode::find($periodeId);
            if ($periode) {
                $filename .= "_Periode_" . str_replace(' ', '_', $periode->definisi);
            }
        }

        $filename .= "_{$date}.xlsx";

        return $filename;
    }
}
