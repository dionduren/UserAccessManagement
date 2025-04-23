<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
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
        // $data = array_values(array_filter($rows, fn($row, $i) => $i !== 0, ARRAY_FILTER_USE_BOTH));
        $data = array_slice($rows, 1); // Skip the header row

        return new StreamedResponse(function () use ($data) {
            $currentCompany = null;
            $processed = 0;
            $totalRows = count($data);
            $lastUpdate = microtime(true);
            $user = Auth::user()?->name ?? 'system';

            echo json_encode(['progress' => 0]) . "\n";
            ob_flush();
            flush();

            foreach ($data as $row) {
                $company = trim($row['A'] ?? '');
                $komp_id = trim($row['B'] ?? '');
                $komp_title = trim($row['C'] ?? '');
                $dept_id = trim($row['D'] ?? '');
                $dept_title = trim($row['E'] ?? '');

                if ($company && !$komp_id && !$dept_id) {
                    $currentCompany = $company;
                    $processed++;
                    continue;
                }

                if ($komp_id && $komp_title && !$dept_id) {
                    \App\Models\Kompartemen::updateOrCreate(
                        ['kompartemen_id' => $komp_id],
                        [
                            'nama' => $komp_title,
                            'company_id' => $currentCompany,
                            'updated_by' => $user,
                            'created_by' => $user,
                        ]
                    );
                }

                if ($komp_id && $komp_title && $dept_id && $dept_title) {
                    \App\Models\Kompartemen::firstOrCreate(
                        ['kompartemen_id' => $komp_id],
                        [
                            'nama' => $komp_title,
                            'company_id' => $currentCompany,
                            'created_by' => $user,
                            'updated_by' => $user,
                        ]
                    );

                    \App\Models\Departemen::updateOrCreate(
                        ['departemen_id' => $dept_id],
                        [
                            'nama' => $dept_title,
                            'company_id' => $currentCompany,
                            'kompartemen_id' => $komp_id,
                            'updated_by' => $user,
                            'created_by' => $user,
                        ]
                    );
                }

                if (!$komp_id && $dept_id && $dept_title) {
                    \App\Models\Departemen::updateOrCreate(
                        ['departemen_id' => $dept_id],
                        [
                            'nama' => $dept_title,
                            'company_id' => $currentCompany,
                            'kompartemen_id' => null,
                            'updated_by' => $user,
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
