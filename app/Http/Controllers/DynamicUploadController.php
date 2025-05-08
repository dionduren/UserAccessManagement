<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\TempUploadSession;

use App\Services\DynamicUploadService;

use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;

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

        $rows = Excel::toArray([], $request->file('excel_file'))[0] ?? [];
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

    public function updateInlineSession(Request $request, $module)
    {
        $modules = config('dynamic_uploads.modules');
        $moduleConfig = $modules[$module];

        $temp = TempUploadSession::where('module', $module)->latest()->first();
        $data = $temp->data ?? [];
        $rowIndex = (int) $request->input('row_index');
        $column = $request->input('column');
        $value = $request->input('value');

        $data[$rowIndex][$column] = $value;

        [$payload,, $errors, $warnings, $cellErrors, $cellWarnings, $enrichedRow] =
            $this->uploadService->processRow($moduleConfig, $data[$rowIndex]);

        $enrichedRow['_row_index'] = $rowIndex;
        $enrichedRow['_row_errors'] = $errors;
        $enrichedRow['_row_warnings'] = $warnings;
        $enrichedRow['_row_issues_count'] = count($errors) + count($warnings);
        $enrichedRow['_cell_errors'] = $cellErrors;
        $enrichedRow['_cell_warnings'] = $cellWarnings;

        $data[$rowIndex] = $enrichedRow;
        $temp->update(['data' => $data]);

        return response()->json(['success' => true, 'updated_row' => $enrichedRow]);
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
