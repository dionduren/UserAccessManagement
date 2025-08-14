<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\Periode;
use App\Models\TempUploadSession;
use App\Services\UserGenericUnitKerjaService;
use App\Imports\UserGenericUnitKerjaPreviewImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class UserGenericUnitKerjaController extends Controller
{
    public function uploadForm()
    {
        $periodes = Periode::orderBy('id')->get();
        return view('imports.upload.user_generic_unit_kerja', compact('periodes'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'periode_id' => 'required|exists:ms_periode,id',
        ]);

        try {
            $import = new UserGenericUnitKerjaPreviewImport();
            Excel::import($import, $request->file('excel_file'));
            $data = $import->rows->map(fn($row) => $row->toArray())->toArray();

            $parsedData = [];
            $periodeId = $request->input('periode_id');

            foreach ($data as $row) {
                $row['periode_id'] = $periodeId;
                $row['_row_errors'] = [];
                $row['_row_warnings'] = [];

                if (empty($row['user_cc'])) {
                    $row['_row_errors'][] = 'User CC wajib diisi.';
                }
                if (empty($row['kompartemen_id'])) {
                    $row['_row_warnings'][] = 'Kompartemen ID kosong.';
                }
                if (empty($row['departemen_id'])) {
                    $row['_row_warnings'][] = 'Departemen ID kosong.';
                }

                $row['_row_issues_count'] = count($row['_row_errors']) + count($row['_row_warnings']);
                $parsedData[] = $row;
            }

            usort($parsedData, fn($a, $b) => ($b['_row_issues_count'] ?? 0) <=> ($a['_row_issues_count'] ?? 0));

            TempUploadSession::create([
                'module' => 'user_generic_unit_kerja_import',
                'data' => $parsedData,
                'periode_id' => $periodeId,
            ]);

            return redirect()->route('user-generic-unit-kerja.previewPage');
        } catch (\Exception $e) {
            Log::error('Excel Preview Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error parsing file: ' . $e->getMessage());
        }
    }

    public function previewPage()
    {
        return view('imports.preview.user_generic_unit_kerja');
    }

    public function getPreviewData()
    {
        $session = TempUploadSession::where('module', 'user_generic_unit_kerja_import')->latest()->first();
        $data = $session ? $session->data : [];

        $kompartemenIds = collect($data)->pluck('kompartemen_id')->filter()->unique()->toArray();
        $departemenIds = collect($data)->pluck('departemen_id')->filter()->unique()->toArray();

        $kompartemenMap = Kompartemen::whereIn('kompartemen_id', $kompartemenIds)->pluck('nama', 'kompartemen_id');
        $departemenMap = Departemen::whereIn('departemen_id', $departemenIds)->pluck('nama', 'departemen_id');

        foreach ($data as &$row) {
            $row['kompartemen_nama'] = $row['kompartemen_id'] ? ($kompartemenMap[$row['kompartemen_id']] ?? null) : null;
            $row['departemen_nama'] = $row['departemen_id'] ? ($departemenMap[$row['departemen_id']] ?? null) : null;
        }
        unset($row);

        if (!$data) {
            return response()->json(['error' => 'No preview data found'], 400);
        }
        return DataTables::of(collect($data))->make(true);
    }

    public function confirmImport(Request $request)
    {
        $session = TempUploadSession::where('module', 'user_generic_unit_kerja_import')->latest()->first();
        $data = $session ? $session->data : [];

        if (!$data) {
            return redirect()->route('user-generic-unit-kerja.upload')->with('error', 'No data available to import.');
        }

        try {
            $response = new StreamedResponse(function () use ($data, $session) {
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

                $service = new UserGenericUnitKerjaService();
                $processed = 0;
                $total = count($data);
                $lastUpdate = microtime(true);

                $send(['progress' => 0]);

                foreach ($data as $row) {
                    try {
                        $service->handleRow($row);
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
                    'message' => 'Data imported successfully',
                    'redirect' => route('user-generic-unit-kerja.upload')
                ]);
            });

            $session?->delete();

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
}
