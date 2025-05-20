<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Http\Request;

use App\Models\TempUploadSession;

use Illuminate\Support\Facades\Log;

use Maatwebsite\Excel\Facades\Excel;
use App\Services\DynamicUploadService;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Excel as ExcelFormat;

class DynamicUploadController extends Controller
{
    protected $uploadService;

    public function __construct(DynamicUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function upload($module)
    {
        $modules = config('dynamic_uploads.modules');
        $moduleConfig = $modules[$module];
        $periodes = Periode::select('id', 'definisi')->get();
        return view('dynamic_upload.upload', compact('module', 'moduleConfig', 'periodes'));
    }

    public function handleUpload(Request $request, $module)
    {
        $modules = config('dynamic_uploads.modules');
        // $moduleConfig = $modules[$module];

        $request->validate(['excel_file' => 'required|mimes:xlsx,xls']);

        $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
            public function array(array $array)
            {
                return $array;
            }
        }, $request->file('excel_file'), ExcelFormat::XLSX)[0] ?? [];
        $header = array_shift($rows);

        $processedRows = [];
        $periodeId = $request->input('periode_id');

        foreach ($rows as $row) {
            $assocRow = array_combine($header, $row);
            $assocRow['periode_id'] = $periodeId;
            $processedRows[] = $assocRow;
        }

        TempUploadSession::create(['module' => $module, 'data' => $processedRows]);
        return redirect()->route('dynamic_upload.preview', $module);
    }

    public function preview($module)
    {
        $modules = config('dynamic_uploads.modules');
        $moduleConfig = $modules[$module];
        $columns = $this->uploadService->generateTabulatorColumns($moduleConfig);
        return view('dynamic_upload.preview', compact('module', 'columns', 'moduleConfig'));
    }

    public function getPreviewData($module)
    {
        $modules = config('dynamic_uploads.modules');
        $moduleConfig = $modules[$module];

        $temp = TempUploadSession::where('module', $module)->latest()->first();
        $data = $temp?->data ?? [];

        $processedData = [];
        foreach ($data as $index => $row) {
            [$payload,, $errors, $warnings, $cellErrors, $cellWarnings, $enrichedRow] =
                $this->uploadService->processRow($moduleConfig, $row);

            $enrichedRow['_row_index'] = $index;
            $enrichedRow['_row_errors'] = $errors;
            $enrichedRow['_row_warnings'] = $warnings;
            $enrichedRow['_row_issues_count'] = count($errors) + count($warnings);
            $enrichedRow['_cell_errors'] = $cellErrors;
            $enrichedRow['_cell_warnings'] = $cellWarnings;

            $processedData[] = $enrichedRow;
        }

        return response()->json(['data' => $processedData]);
    }

    // public function submitAll(Request $request, $module)
    public function submitAll($module)
    {
        $modules = config('dynamic_uploads.modules');
        abort_unless(isset($modules[$module]), 404);

        $moduleConfig = $modules[$module];

        $temp = TempUploadSession::where('module', $module)->latest()->first();
        if (!$temp) {
            return response()->json(['error' => 'No session found'], 404);
        }

        $rows = $temp->data ?? [];
        $savedCount = 0;
        $skippedRows = [];

        foreach ($rows as $index => $row) {
            $skipReasons = [];

            // convert ke id dari masing-masing value (untuk company)
            $payload = $this->uploadService->transformPayload($moduleConfig, $row);

            // ambil parameter & error
            [, $where, $errors] = $this->uploadService->processRow($moduleConfig, $payload);


            // validation untuk kompartemen dan departemen yang belum terdaftar
            if (
                !empty($payload['kompartemen_id']) &&
                !Kompartemen::where('kompartemen_id', $payload['kompartemen_id'])->exists()
            ) {
                $skipReasons[] = "ID Kompartemen {$payload['kompartemen_id']} belum terdaftar";
            }

            if (
                !empty($payload['departemen_id']) &&
                !Departemen::where('departemen_id', $payload['departemen_id'])->exists()
            ) {
                $skipReasons[] = "ID Departemen {$payload['departemen_id']} belum terdaftar";
            }

            if (count($errors) > 0) {
                $skipReasons[] = "Validation errors: " . implode('; ', $errors);
            }

            if (count($skipReasons) > 0) {
                $skippedRows[] = [
                    'row' => $index + 1,
                    'payload' => $payload,
                    'reasons' => $skipReasons,
                ];
                continue;
            }


            // set null untuk value yang kosong
            if (empty($payload['departemen_id'])) {
                $payload['departemen_id'] = null;
            }

            if (empty($payload['kompartemen_id'])) {
                $payload['kompartemen_id'] = null;
            }

            // Log::info($payload);


            // if (count($errors) === 0) {
            $this->uploadService->submitRow($moduleConfig, $payload, $where);
            $savedCount++;
            // }
        }

        return response()->json([
            'success' => true,
            'saved' => $savedCount,
            'skipped' => count($skippedRows),
            'skipped_details' => $skippedRows,
        ], 200, ['Content-Type' => 'application/json']);
    }
}
