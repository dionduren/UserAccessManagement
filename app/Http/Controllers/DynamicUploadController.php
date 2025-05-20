<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TempUploadSession;
use App\Models\Periode;
use App\Services\DynamicUploadService;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class DynamicUploadController extends Controller
{
    protected $uploadService;

    public function __construct(DynamicUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    private function getModuleConfig($module)
    {
        $modules = config('dynamic_uploads.modules');
        abort_unless(isset($modules[$module]), 404);
        return $modules[$module];
    }

    public function upload($module)
    {
        $moduleConfig = $this->getModuleConfig($module);
        $periodes = Periode::select('id', 'definisi')->get();
        return view('dynamic_upload.upload', compact('module', 'moduleConfig', 'periodes'));
    }

    public function handleUpload(Request $request, $module)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls']);
        $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
            public function array(array $array)
            {
                return $array;
            }
        }, $request->file('excel_file'), ExcelFormat::XLSX)[0] ?? [];

        $header = array_shift($rows);
        $periodeId = $request->input('periode_id');
        $usePeriode = $moduleConfig['uses_periode'] ?? false;

        $processed = array_map(function ($row) use ($header, $periodeId, $usePeriode) {
            $combined = array_combine($header, $row);
            if ($usePeriode) {
                $combined['periode_id'] = $periodeId;
            }
            return $combined;
        }, $rows);

        TempUploadSession::create(['module' => $module, 'data' => $processed, 'periode_id' => $periodeId]);
        return redirect()->route('dynamic_upload.preview', $module);
    }

    public function preview($module)
    {
        $moduleConfig = $this->getModuleConfig($module);
        $columns = $this->uploadService->generateTabulatorColumns($moduleConfig);
        return view('dynamic_upload.preview', compact('module', 'columns', 'moduleConfig'));
    }

    public function getPreviewData($module)
    {
        $moduleConfig = $this->getModuleConfig($module);
        $data = TempUploadSession::where('module', $module)->latest()->first()?->data ?? [];
        return response()->json(['data' => array_map(fn($row, $i) => array_merge(
            ['_row_index' => $i],
            $this->uploadService->handle($moduleConfig, $row)[6]
        ), $data, array_keys($data))]);
    }

    public function submitAll($module)
    {
        $moduleConfig = $this->getModuleConfig($module);
        $temp = TempUploadSession::where('module', $module)->latest()->first();

        if (!$temp) {
            return response()->json(['error' => 'No session found'], 404);
        }

        $periodeId = $temp->periode_id;
        $rows = $temp->data ?? [];

        $saved = 0;
        $skipped = [];

        foreach ($rows as $index => $row) {
            // Inject periode_id if missing
            if (($moduleConfig['uses_periode'] ?? false) && empty($row['periode_id'])) {
                $row['periode_id'] = $periodeId;
            }

            [$payload, $where, $errors,,,, $previewRow] = $this->uploadService->handle($moduleConfig, $row);

            // ✅ Validate all required `where_fields` + optional periode_id
            $missingKeys = [];
            foreach ($moduleConfig['where_fields'] as $key) {
                if (empty($where[$key])) $missingKeys[] = $key;
            }
            if (($moduleConfig['uses_periode'] ?? false) && empty($where['periode_id'])) {
                $missingKeys[] = 'periode_id';
            }

            if (count($missingKeys)) {
                $skipped[] = [
                    'row' => $index + 1,
                    'payload' => $payload,
                    'reasons' => ['Missing keys: ' . implode(', ', $missingKeys)],
                ];
                continue;
            }

            $this->uploadService->submitRow($moduleConfig, $payload, $where);
            $saved++;
        }

        return response()->json([
            'success' => true,
            'saved' => $saved,
            'skipped' => count($skipped),
            'skipped_details' => $skipped,
        ]);
    }
}
