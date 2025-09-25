<?php

namespace App\Http\Controllers\IOExcel;

use App\Exports\JobRoleCompositeTemplateExport;

use App\Http\Controllers\Controller;

use App\Imports\CompanyKompartemenPreviewImport;
use App\Models\Company;
use App\Services\CompanyKompartemenService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class CompanyKompartemenController extends Controller
{
    public function uploadForm()
    {
        return view('imports.upload.company_kompartemen');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        try {
            $import = new CompanyKompartemenPreviewImport();
            Excel::import($import, $request->file('excel_file'));
            $data = $import->rows;

            $parsedData = [];
            $errors = [];

            foreach ($data as $index => $row) {
                $validator = Validator::make($row->toArray(), [
                    'company' => 'required|string',
                    'kompartemen_id' => 'nullable',
                    'kompartemen' => 'nullable|string',
                    'departemen_id' => 'nullable',
                    'departemen' => 'nullable|string',
                    'job_function' => 'required|string',
                    'composite_role' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    $errors[$index + 1] = $validator->errors()->all();
                    Log::error("Validation failed row {$index}", ['errors' => $validator->errors()->all()]);
                    continue;
                }

                $company = Company::where('company_code', $row['company'])->first();
                $parsedData[] = [
                    'company_code' => $row['company'],
                    'company_name' => $company->nama ?? null,
                    'kompartemen_id' => $row['kompartemen_id'] ?? null,
                    'kompartemen' => $row['kompartemen'] ?? null,
                    'departemen_id' => $row['departemen_id'] ?? null,
                    'departemen' => $row['departemen'] ?? null,
                    'job_function' => $row['job_function'],
                    'composite_role' => $row['composite_role'],
                    'status' => $row['status'] ?? ['type' => 'valid', 'message' => '']
                ];
            }

            if ($errors) {
                return back()->with('validationErrors', $errors);
            }

            session(['parsedData' => $parsedData]);
            return view('imports.preview.company_kompartemen');
        } catch (\Exception $e) {
            Log::error('Excel Preview Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error parsing file: ' . $e->getMessage());
        }
    }

    public function getPreviewData()
    {
        $data = session('parsedData');

        if (!$data) {
            return redirect()->route('company_kompartemen.upload')->with('error', 'No preview data found. Please upload a file first.');
        }

        return DataTables::of(collect($data))
            ->addColumn('row_class', function ($row) {
                if (isset($row['status']) && $row['status']['type'] === 'error') {
                    return 'error-row';
                }
                if (isset($row['status']) && $row['status']['type'] === 'warning') {
                    return 'warning-row';
                }
                return '';
            })
            ->addColumn('status_message', function ($row) {
                return $row['status']['message'] ?? '';
            })
            ->addColumn('status_type', function ($row) {
                if (isset($row['status'])) {
                    switch ($row['status']['type']) {
                        case 'error':
                            return 2;
                        case 'warning':
                            return 1;
                        default:
                            return 0;
                    }
                }
                return 0;
            })
            ->rawColumns(['row_class'])
            ->make(true);
    }

    public function confirmImport()
    {
        $data = session('parsedData');

        if (!$data) {
            return redirect()->route('company_kompartemen.upload')->with('error', 'No data available to import.');
        }

        try {
            $response = new StreamedResponse(function () use ($data) {
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
                $total = count($data);
                $lastUpdate = microtime(true);

                $send(['progress' => 0]);

                foreach ($data as $row) {
                    try {
                        (new CompanyKompartemenService())->handleRow($row);
                    } catch (\Exception $e) {
                        Log::error('Row import failed', [
                            'row' => $row,
                            'error' => $e->getMessage()
                        ]);
                    }

                    $processed++;
                    if (microtime(true) - $lastUpdate >= 1 || $processed === $total) {
                        $send(['progress' => (int) round($processed / max(1, $total) * 100)]);
                        $lastUpdate = microtime(true);
                    }
                }

                $send(['success' => 'Data imported successfully']);
            });

            session()->forget('parsedData');

            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('Cache-Control', 'no-cache, no-transform');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Connection', 'keep-alive');

            return $response;
        } catch (\Exception $e) {
            Log::error('Error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'details' => [
                    'error_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $companyCode = Auth::user()?->loginDetail?->company_code ?? 'A000';

        return Excel::download(new JobRoleCompositeTemplateExport($companyCode), 'job_role_composite_role_template.xlsx');
    }
}
