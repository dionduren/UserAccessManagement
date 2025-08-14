<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyMasterDataController extends Controller
{
    public function showForm()
    {
        return view('imports.upload.company_master_data');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $spreadsheet = IOFactory::load($request->file('excel_file'));
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $headers = array_map('strtolower', $rows[1]); // assumes first row has headers
        $columnMap = [];
        foreach ($headers as $colLetter => $headerName) {
            $columnMap[$colLetter] = trim($headerName);
        }

        $data = array_slice($rows, 1); // skip header row (row 1) + titles

        // $data = array_values(array_filter($rows, fn($row, $i) => $i !== 0, ARRAY_FILTER_USE_BOTH));
        // $data = array_slice($rows, 1); // Skip the header row

        return new StreamedResponse(function () use ($data, $columnMap) {
            $currentCompany = null;
            $processed = 0;
            $totalRows = count($data);
            $lastUpdate = microtime(true);
            $user = Auth::user()?->name ?? 'system';

            echo json_encode(['progress' => 0]) . "\n";
            ob_flush();
            flush();

            foreach ($data as $row) {
                $company = trim($row[array_search('company', $columnMap)] ?? '');
                $dir_id = trim($row[array_search('dir_id', $columnMap)] ?? '');
                $dir_title = trim($row[array_search('dir_title', $columnMap)] ?? '');
                $komp_id = trim($row[array_search('komp_id', $columnMap)] ?? '');
                $komp_title = trim($row[array_search('komp_title', $columnMap)] ?? '');
                $dept_id = trim($row[array_search('dept_id', $columnMap)] ?? '');
                $dept_title = trim($row[array_search('dept_title', $columnMap)] ?? '');
                $cost_center = trim($row[array_search('cost_center', $columnMap)] ?? '');
                $cost_code = trim($row[array_search('cost_code', $columnMap)] ?? '');

                // Log::info($company, [
                //     'dir_id' => $dir_id,
                //     'dir_title' => $dir_title,
                //     'komp_id' => $komp_id,
                //     'komp_title' => $komp_title,
                //     'dept_id' => $dept_id,
                //     'dept_title' => $dept_title,
                //     'cost_center' => $cost_center,
                //     'cost_code' => $cost_code,
                // ]);

                if ($company && !$komp_id && !$dept_id) {
                    // $currentCompany = $company;
                    $processed++;
                    CostCenter::updateOrCreate(
                        [
                            'cost_center' => $cost_center,
                            'level_id' => $dir_id,
                        ],
                        [
                            'company_id' => $company,
                            'parent_id' => $company,
                            'level' => 'Direktorat',
                            'level_name' => $dir_title,
                            'cost_center' => $cost_center,
                            'cost_code' => $cost_code,
                            'created_by' => $user,
                        ]
                    );
                    continue;
                }

                if ($komp_id && $komp_title && !$dept_id) {
                    Kompartemen::updateOrCreate(
                        ['kompartemen_id' => $komp_id],
                        [
                            'nama' => $komp_title,
                            'company_id' => $company,
                            'cost_center' => $cost_center ?? 'N/A',
                            'created_by' => $user,
                        ]
                    );

                    CostCenter::updateOrCreate(
                        [
                            'cost_center' => $cost_center,
                            'level' => 'Kompartemen',
                            'level_id' => $komp_id,
                        ],
                        [
                            'company_id' => $company,
                            'parent_id' => $dir_id,
                            'level_name' => $komp_title,
                            'cost_center' => $cost_center,
                            'cost_code' => $cost_code,
                            'created_by' => $user,
                        ]
                    );
                }

                if ($komp_id && $komp_title && $dept_id && $dept_title) {
                    Kompartemen::firstOrCreate(
                        ['kompartemen_id' => $komp_id],
                        [
                            'nama' => $komp_title,
                            'cost_center' => $cost_center ?? 'N/A',
                            'company_id' => $company,
                            'created_by' => $user,
                        ]
                    );

                    Departemen::updateOrCreate(
                        ['departemen_id' => $dept_id],
                        [
                            'nama' => $dept_title,
                            'company_id' => $company,
                            'kompartemen_id' => $komp_id,
                            'cost_center' => $cost_center ?? 'N/A',
                            'created_by' => $user,
                        ]
                    );

                    CostCenter::updateOrCreate(
                        [
                            'cost_center' => $cost_center,
                            'level' => 'Departemen',
                            'level_id' => $dept_id,
                        ],
                        [
                            'company_id' => $company,
                            'parent_id' => $komp_id,
                            'level_name' => $dept_title,
                            'cost_center' => $cost_center,
                            'cost_code' => $cost_code,
                            'created_by' => $user,
                        ]
                    );
                }

                if (!$komp_id && $dept_id && $dept_title) {
                    Departemen::updateOrCreate(
                        ['departemen_id' => $dept_id],
                        [
                            'nama' => $dept_title,
                            'company_id' => $company,
                            'kompartemen_id' => null,
                            'cost_center' => $cost_center ?? 'N/A',
                            'created_by' => $user,
                        ]
                    );

                    CostCenter::updateOrCreate(
                        [
                            'cost_center' => $cost_center,
                            'level' => 'Departemen',
                            'level_id' => $dept_id,
                        ],
                        [
                            'company_id' => $company,
                            'parent_id' => $company,
                            'level' => 'Departemen',
                            'level_id' => $dept_id,
                            'level_name' => $dept_title,
                            'cost_center' => $cost_center,
                            'cost_code' => $cost_code,
                            'created_by' => $user,
                        ]
                    );
                }

                $processed++;
                if (microtime(true) - $lastUpdate >= 1 || $processed === $totalRows) {
                    echo json_encode(['progress' => round($processed / $totalRows * 100)]) . "\n";
                    ob_flush();
                    flush();
                    $lastUpdate = microtime(true);
                }
            }

            echo json_encode(['success' => 'Upload complete']) . "\n";
            ob_flush();
            flush();
        }, 200, ['Content-Type' => 'text/event-stream']);
    }
}
