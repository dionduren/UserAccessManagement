<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\CompanyMasterDataTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class CompanyMasterDataController extends Controller
{
    public function showForm()
    {
        return view('imports.upload.company_master_data');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('excel_file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $headers = array_map('strtolower', $rows[1] ?? []);
        $columnMap = [];
        foreach ($headers as $colLetter => $headerName) {
            $columnMap[$colLetter] = trim($headerName);
        }

        $data = array_slice($rows, 1);

        return response()->stream(function () use ($data, $columnMap) {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');
            @set_time_limit(0);
            @ob_implicit_flush(true);
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }

            $send = function (array $payload) {
                echo json_encode($payload) . "\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            };

            $processed = 0;
            $totalRows = count($data);
            $lastUpdate = microtime(true);
            $user = Auth::user()?->name ?? 'system';

            $send(['progress' => 0]);

            foreach ($data as $row) {
                $company     = trim($row[array_search('company', $columnMap, true)] ?? '');
                $dir_id      = trim($row[array_search('dir_id', $columnMap, true)] ?? '');
                $dir_title   = trim($row[array_search('dir_title', $columnMap, true)] ?? '');
                $komp_id     = trim($row[array_search('komp_id', $columnMap, true)] ?? '');
                $komp_title  = trim($row[array_search('komp_title', $columnMap, true)] ?? '');
                $dept_id     = trim($row[array_search('dept_id', $columnMap, true)] ?? '');
                $dept_title  = trim($row[array_search('dept_title', $columnMap, true)] ?? '');
                $cost_center = trim($row[array_search('cost_center', $columnMap, true)] ?? '');
                $cost_code   = trim($row[array_search('cost_code', $columnMap, true)] ?? '');

                if ($company && !$komp_id && !$dept_id) {
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
                    $processed++;
                    if (microtime(true) - $lastUpdate >= 1 || $processed === $totalRows) {
                        $send(['progress' => (int) round($processed / max(1, $totalRows) * 100)]);
                        $lastUpdate = microtime(true);
                    }
                    continue;
                }

                if ($komp_id && $komp_title && !$dept_id) {
                    Kompartemen::updateOrCreate(
                        ['kompartemen_id' => $komp_id],
                        [
                            'nama' => $komp_title,
                            'company_id' => $company,
                            'cost_center' => $cost_center ?: 'N/A',
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
                            'cost_center' => $cost_center ?: 'N/A',
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
                            'cost_center' => $cost_center ?: 'N/A',
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
                            'cost_center' => $cost_center ?: 'N/A',
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
                    $send(['progress' => (int) round($processed / max(1, $totalRows) * 100)]);
                    $lastUpdate = microtime(true);
                }
            }

            $send(['success' => 'Upload complete']);
        }, 200, ['Content-Type' => 'text/event-stream']);
    }

    public function downloadMasterData()
    {
        return Excel::download(new CompanyMasterDataTemplateExport(), 'master_unit_kerja.xlsx');
    }
}
