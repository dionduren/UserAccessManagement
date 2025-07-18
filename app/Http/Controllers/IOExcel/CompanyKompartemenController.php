<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use App\Models\Company;

use App\Imports\CompanyKompartemenPreviewImport;
use App\Services\CompanyKompartemenService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;

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
                    'composite_role' => 'required|string',
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

    // public function getPreviewData()
    // {
    //     $data = session('parsedData');

    //     if (!$data) {
    //         return response()->json(['error' => 'No preview data found'], 400);
    //     }

    //     return DataTables::of(collect($data))->make(true);
    // }

    public function getPreviewData()
    {
        $data = session('parsedData');

        if (!$data) {
            // Optionally redirect instead of returning JSON error
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
                $processed = 0;
                $total = count($data);
                $lastUpdate = microtime(true);

                echo json_encode(['progress' => 0]) . "\n";
                ob_flush();
                flush();

                foreach ($data as $row) {
                    try {
                        $service = new CompanyKompartemenService();
                        $service->handleRow($row);
                    } catch (\Exception $e) {
                        Log::error('Row import failed', ['row' => $row, 'error' => $e->getMessage()]);
                    }

                    $processed++;
                    if (microtime(true) - $lastUpdate >= 1 || $processed === $total) {
                        echo json_encode(['progress' => round(($processed / $total) * 100)]) . "\n";
                        ob_flush();
                        flush();
                        $lastUpdate = microtime(true);
                    }
                }

                echo json_encode(['success' => 'Data imported successfully']) . "\n";
                ob_flush();
                flush();
            });

            session()->forget('parsedData');
            $response->headers->set('Content-Type', 'text/event-stream');
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
}
