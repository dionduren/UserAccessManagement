<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use App\Exports\UserGenericTemplateExport;
use App\Services\UserSystemService;

use App\Models\Periode;
use App\Models\TempUploadSession;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class UserSystemImportController extends Controller
{
    public function index()
    {
        $periodes = Periode::select('id', 'definisi')->orderBy('definisi')->get();
        return view('imports.user_system.upload', compact('periodes'));
    }

    public function template()
    {
        return Excel::download(new UserGenericTemplateExport(), 'user_system_template.xlsx');
    }

    // POST upload -> parse -> store rows in temp_upload_sessions (no file path kept for preview)
    public function preview(Request $request)
    {
        $request->validate([
            'file'       => 'required|file|mimes:xlsx,xls',
            'periode_id' => 'required|exists:ms_periode,id'
        ]);

        // Read sheet UPLOAD_TEMPLATE (fallback to first if not found)
        $collections = Excel::toCollection(
            new class implements \Maatwebsite\Excel\Concerns\WithHeadingRow, \Maatwebsite\Excel\Concerns\ToCollection {
                public function collection(\Illuminate\Support\Collection $rows) {}
            },
            $request->file('file')
        );

        $sheet = null;
        foreach ($collections as $name => $c) {
            if (strtoupper($name) === 'UPLOAD_TEMPLATE') {
                $sheet = $c;
                break;
            }
        }
        if (!$sheet) {
            $sheet = $collections->first();
        }

        if (!$sheet || $sheet->count() === 0) {
            return back()->with('error', 'Sheet UPLOAD_TEMPLATE kosong / tidak ditemukan.');
        }

        // Normalize rows & attach periode_id (do NOT validate deeply here)
        $normalized = [];
        foreach ($sheet as $row) {
            $r = $row->toArray();
            // skip fully empty lines
            if (empty(array_filter($r, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }
            $r['periode_id'] = (int)$request->periode_id;
            $normalized[] = $r;
        }

        if (count($normalized) === 0) {
            return back()->with('error', 'Tidak ada baris valid untuk dipreview.');
        }

        // Persist into temp session (JSON)
        $session = TempUploadSession::create([
            'module'     => 'user-system',
            'periode_id' => $request->periode_id,
            'data'       => $normalized
        ]);

        return redirect()->route('user_system.import.preview.get', [
            'session_id' => $session->id
        ]);
    }

    // GET preview from TempUploadSession
    public function previewGet(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('user_system.import.index')
                ->with('error', 'Session tidak ditemukan.');
        }

        $session = TempUploadSession::where('module', 'user-system')->find($sessionId);
        if (!$session) {
            return redirect()->route('user_system.import.index')
                ->with('error', 'Session upload tidak valid / kedaluwarsa.');
        }

        $rows       = collect($session->data);
        $periode_id = $session->periode_id;

        $firstRow = $rows->first();
        if (is_array($firstRow)) {
            $columns = array_keys($firstRow);
        } elseif ($firstRow instanceof \Illuminate\Support\Collection) {
            $columns = $firstRow->keys()->toArray();
        } else {
            $columns = [];
        }

        return view('imports.user_system.preview', [
            'rows'        => $rows,
            'session'     => $session,
            'periode_id'  => $periode_id,
            'columns'     => $columns,
            'session_id'  => $session->id,
        ]);
    }

    // Stream confirm using rows stored in TempUploadSession
    public function confirm(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:temp_upload_sessions,id'
        ]);

        $session = TempUploadSession::where('module', 'user-system')->find($request->session_id);
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $rows      = $session->data ?? [];
        $periodeId = $session->periode_id;

        $service = new UserSystemService();

        $response = new StreamedResponse(function () use ($rows, $periodeId, $service, $session) {
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

            $total = count($rows);
            $processed = 0;
            $lastPush = microtime(true);
            $send(['progress' => 0, 'processed' => 0, 'total' => $total]);

            try {
                foreach ($rows as $r) {
                    $r['periode_id'] = $periodeId; // enforce
                    if (empty($r['user_code'])) {
                        $processed++;
                        continue;
                    }
                    try {
                        $service->handleRow($r);
                    } catch (\Throwable $e) {
                        Log::error('UserSystem row failure', ['row' => $r, 'err' => $e->getMessage()]);
                    }
                    $processed++;

                    if (microtime(true) - $lastPush >= 0.6 || $processed === $total) {
                        $send([
                            'progress'  => (int)round($processed / max(1, $total) * 100),
                            'processed' => $processed,
                            'total'     => $total
                        ]);
                        $lastPush = microtime(true);
                    }
                }

                // Clean up session (optional)
                $session->delete();

                $send([
                    'success'  => true,
                    'message'  => 'Import selesai',
                    'progress' => 100,
                    'redirect' => route('user-system.index')
                ]);
            } catch (\Throwable $e) {
                Log::error('UserSystem confirm fatal', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'msg' => $e->getMessage()
                ]);
                $send([
                    'error' => true,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}
