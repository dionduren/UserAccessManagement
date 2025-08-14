<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use App\Exports\NIKJobRoleTemplate;
use App\Imports\NIKJobRoleImport;
use App\Models\Periode;
use App\Models\NIKJobRole;
use App\Models\JobRole; // added

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class NIKJobRoleImportController extends Controller
{
    public function uploadForm()
    {
        $periodes = Periode::select('id', 'definisi')->get();
        return view('upload.nik_job_role.upload', compact('periodes'));
    }


    public function downloadTemplate()
    {
        return Excel::download(new NIKJobRoleTemplate, '4.template_nik_job_role.xlsx');
    }

    public function store(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|numeric|exists:ms_periode,id',
            'excel_file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('excel_file');
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid() . '.' . $extension;
        $filePath = $file->storeAs('temp', $filename);
        $absolutePath = storage_path('app/' . $filePath);

        try {
            // Parse Excel rows clearly:
            $excelRows = Excel::toArray(new NIKJobRoleImport, $absolutePath)[0];

            // Structure your session explicitly:
            $periodeId = $request->input('periode_id');

            session()->put('nikJobRoleUpload', [
                'periode_id' => $periodeId,
                'data' => $excelRows,
            ]);

            return redirect()->route('nik_job_role.upload.preview');
        } catch (\Maatwebsite\Excel\Exceptions\NoTypeDetectedException $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'No data available in the uploaded Excel file.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function preview(Request $request)
    {
        $data = session('nikJobRoleUpload');

        // \dd($data);

        if (!$data) {
            return redirect()->back()->with('error', 'No data available in session.');
        }

        try {
            // Process the data from session
            $parsedData = $data['data'];
            $periodeId = $data['periode_id'];

            // Validate and parse each row
            $errors = [];
            $previewData = [];

            foreach ($parsedData as $index => $row) {
                // Custom validation for each row (adjust rules as needed)
                $validator = Validator::make($row, [
                    'nik' => 'required',
                    'job_role' => 'required'
                ]);

                if ($validator->fails()) {
                    $errorDetails = [
                        'row' => $index + 1,
                        'errors' => $validator->errors()->all(),
                    ];
                    $errors[$index + 1] = $validator->errors()->all();

                    // Log the validation errors with details
                    Log::error('Validation failed for User NIK data', $errorDetails);
                } else {
                    // Store validated data along with derived company name for preview
                    $previewData[] = [
                        'periode_id' => $periodeId,
                        'nik' => $row['nik'],
                        'job_role' => $row['job_role'],
                    ];
                }
            }

            if (!empty($errors)) {
                Log::error('Validation errors occurred.', ['errors' => $errors]);
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['excel_errors' => $errors])
                    ->with('error', 'Validation errors occurred. Please check the highlighted rows.');
            }

            // Store the parsed data in session for confirmation (or use another mechanism to pass data)
            session([
                'parsedData' => [
                    'periode_id' => $periodeId,
                    'data' => $previewData
                ]
            ]);


            return view('upload.nik_job_role.preview', ['parsedData' => $previewData]);
        } catch (\Exception $e) {
            Log::error('Error during Preview', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Temporary Debugging only:
            return redirect()->back()->with('error', 'Error during Preview: ' . $e->getMessage());
        }
    }

    public function getPreviewData()
    {
        $parsedData = session('nikJobRoleUpload');

        if (!$parsedData || !isset($parsedData['data']) || !isset($parsedData['periode_id'])) {
            return response()->json(['data' => []]);
        }

        $dataWithIds = [];
        foreach ($parsedData['data'] as $index => $row) {
            $row['DT_RowId'] = 'row_' . $index;
            $row['_row_index'] = $index;
            $row['periode'] = $parsedData['periode_id'];
            $dataWithIds[] = $row;
        }

        return DataTables::of($dataWithIds)->make(true);
    }

    public function updateInlineSession(Request $request)
    {
        $rowIndex = (int) $request->input('row_index');
        $column = $request->input('column');
        $value = $request->input('value');

        $parsedData = session('nikJobRoleUpload');

        if (!isset($parsedData['data'][$rowIndex])) {
            return response()->json(['error' => 'Invalid row index provided.'], 400);
        }

        $parsedData['data'][$rowIndex][$column] = $value;

        session(['nikJobRoleUpload' => $parsedData]);

        return response()->json(['success' => true]);
    }

    // Streaming confirm import
    public function confirmImport(Request $request)
    {
        $parsed = session('parsedData'); // set in preview()
        if (!$parsed || !isset($parsed['data'], $parsed['periode_id'])) {
            return redirect()->route('nik_job_role.upload.form')->with('error', 'No data available for import.');
        }

        $periodeId = $parsed['periode_id'];
        $rows = $parsed['data'];
        $total = count($rows);

        try {
            $response = new StreamedResponse(function () use ($rows, $periodeId, $total) {
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

                $send(['progress' => 0]);
                $processed = 0;
                $lastUpdate = microtime(true);

                foreach ($rows as $row) {
                    try {
                        $jobRoleId = JobRole::where('nama_jabatan', $row['job_role'])->value('id');
                        if ($jobRoleId) {
                            NIKJobRole::updateOrCreate(
                                [
                                    'periode_id' => $periodeId,
                                    'nik' => $row['nik'],
                                    'job_role_id' => $jobRoleId
                                ],
                                [
                                    'is_active' => true,
                                    'last_update' => Carbon::now()
                                ]
                            );
                        } else {
                            Log::warning('Job Role not found', ['job_role' => $row['job_role'], 'nik' => $row['nik']]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Row import failed', ['row' => $row, 'error' => $e->getMessage()]);
                    }

                    $processed++;
                    if (microtime(true) - $lastUpdate >= 1 || $processed === $total) {
                        $send(['progress' => (int) round($processed / max(1, $total) * 100)]);
                        $lastUpdate = microtime(true);
                    }
                }

                $send([
                    'success' => true,
                    'message' => 'Import completed',
                    'redirect' => route('nik_job_role.upload.form')
                ]);
            });

            session()->forget('parsedData');

            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('Cache-Control', 'no-cache, no-transform');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Connection', 'keep-alive');

            return $response;
        } catch (\Exception $e) {
            Log::error('Confirm import failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function submitSingle(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required',
            'job_role_id' => 'required|exists:tr_job_roles,id',
            'periode' => 'required|exists:ms_periode,definisi'
        ]);

        // Check DB for NIK duplicates
        if (NIKJobRole::where('nik', $validated['nik'])->exists()) {
            return response()->json(['message' => 'NIK already exists in DB'], 422);
        }

        $periode = Periode::where('definisi', $validated['periode'])->first();

        NIKJobRole::create([
            'nik' => $validated['nik'],
            'job_role_id' => $validated['job_role_id'],
            'periode_id' => $periode->id,
            'is_active' => true,
            'last_update' => Carbon::now()
        ]);

        return response()->json(['message' => 'Row successfully submitted']);
    }

    public function submitAll(Request $request)
    {
        $data = $request->all();
        $errors = [];

        foreach ($data as $index => $row) {
            $validator = Validator::make($row, [
                'nik' => 'required',
                'job_role_id' => 'required|exists:tr_job_roles,id',
                'periode_id' => 'required|exists:ms_periode,id'
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors();
                continue;
            }

            if (!NIKJobRole::where('nik', $row['nik'])->exists()) {
                NIKJobRole::create([
                    'nik' => $row['nik'],
                    'job_role_id' => $row['job_role_id'],
                    'periode_id' => $row['periode_id'],
                    'is_active' => true,
                    'last_update' => Carbon::now()
                ]);
            } else {
                $errors[$index] = ['NIK already exists'];
            }
        }

        if (!empty($errors)) {
            return response()->json(['message' => 'Some rows failed validation', 'errors' => $errors], 422);
        }

        return response()->json(['message' => 'All rows successfully submitted']);
    }
}
