<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;

use App\Imports\UserNIKImport;

use App\Models\MasterDataKaryawanLocal;
use App\Models\Company;
use App\Models\UserNIK;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class UserNIKImportController extends Controller
{
  /**
   * Store a newly created resource in storage.
   */
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
      $excelRows = Excel::toArray(new UserNIKImport, $absolutePath)[0];

      // Structure your session explicitly:
      $periodeId = $request->input('periode_id');

      session()->put('parsedData', [
        'periode_id' => $periodeId,
        'data' => $excelRows,
      ]);

      return redirect()->route('user-nik.upload.preview');
    } catch (NoTypeDetectedException $e) {
      Log::error($e->getMessage());
      return redirect()->back()->with('error', 'No data available in the uploaded Excel file.');
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return redirect()->back()->with('error', $e->getMessage());
    }
  }


  public function preview(Request $request)
  {
    $data = session('parsedData');

    if (!$data) {
      return redirect()->back()->with('error', 'No data available in session.');
    }

    try {
      $parsedData = $data['data'];
      $periodeId = $data['periode_id'];

      $errors = [];
      $previewData = [];

      foreach ($parsedData as $index => $row) {
        $validator = Validator::make($row, [
          'group' => 'nullable',
          'user_code' => 'required',
          'license_type' => 'required',
          'last_login' => 'nullable',
          'valid_from' => 'nullable',
          'valid_to' => 'nullable'
        ]);

        if ($validator->fails()) {
          $errorDetails = [
            'row' => $index + 1,
            'errors' => $validator->errors()->all(),
          ];
          $errors[$index + 1] = $validator->errors()->all();

          Log::error('Validation failed for User NIK data', $errorDetails);
        } else {
          $company = Company::where('shortname', $row['group'])->first();

          if (!$company) {
            $company = Company::find($row['group']);
            if (!$company) {
              $errorDetails = [
                'row' => $index + 1,
                'group' => $row['group'],
              ];
              Log::error('Company not found for User NIK data', $errorDetails);
              $errors[$index + 1] = ["Company not found for group: {$row['group']}"];
            }
          } else {
            $userNIKExists = MasterDataKaryawanLocal::where('nik', $row['user_code'])->exists();
            if (!$userNIKExists) {
              $previewData[] = [
                'periode_id' => $periodeId,
                'group' => $company->company_code,
                'user_code' => $row['user_code'],
                'user_type' => "NIK",
                'license_type' => $row['license_type'],
                'last_login' => $row['last_login'] ? date('Y-m-d', strtotime(str_replace('.', '-', $row['last_login']))) : null,
                'valid_from' => $row['valid_from'] ? date('Y-m-d', strtotime(str_replace('.', '-', $row['valid_from']))) : null,
                'valid_to' => $row['valid_to'] ? date('Y-m-d', strtotime(str_replace('.', '-', $row['valid_to']))) : null,
                'flagged' => true,
                'keterangan' => 'User NIK belum ada pada mapping User Detail',
              ];
              continue;
            }
            $previewData[] = [
              'periode_id' => $periodeId,
              'group' => $company->company_code,
              'user_code' => $row['user_code'],
              'user_type' => "NIK",
              'license_type' => $row['license_type'],
              'last_login' => $row['last_login'] ? date('Y-m-d', strtotime(str_replace('.', '-', $row['last_login']))) : null,
              'valid_from' => $row['valid_from'] ? date('Y-m-d', strtotime(str_replace('.', '-', $row['valid_from']))) : null,
              'valid_to' => $row['valid_to'] ? date('Y-m-d', strtotime(str_replace('.', '-', $row['valid_to']))) : null,
              'flagged' => false,
              'keterangan' => null,
            ];
          }
        }
      }

      if (!empty($errors)) {
        Log::error('Validation errors occurred.', ['errors' => $errors]);
        return redirect()->back()
          ->withInput()
          ->withErrors(['excel_errors' => $errors])
          ->with('error', 'Validation errors occurred. Please check the highlighted rows.');
      }

      session(['parsedData' => [
        'periode_id' => $periodeId,
        'data' => $previewData
      ]]);

      return view('upload.user_nik.preview', ['previewData' => $previewData]);
    } catch (\Exception $e) {
      Log::error('Error during Preview', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return redirect()->back()->with('error', 'Error during Preview: ' . $e->getMessage());
    }
  }


  /**
   * Get the data for the preview table
   */
  public function getPreviewData()
  {
    $parsedData = session('parsedData');

    if (!$parsedData || !isset($parsedData['data']) || !isset($parsedData['periode_id'])) {
      return response()->json(['data' => []]);
    }

    $dataWithIds = [];
    foreach ($parsedData['data'] as $index => $row) {
      $row['DT_RowId'] = 'row_' . $index;
      $row['_row_index'] = $index;
      $dataWithIds[] = $row;
    }

    return DataTables::of($dataWithIds)->make(true);
  }

  // public function updateInlineSession(Request $request)
  // {
  //   $rowIndex = (int) $request->input('row_index');
  //   $column = $request->input('column');
  //   $value = $request->input('value');

  //   $parsedData = session('parsedData');

  //   if (!isset($parsedData['data'][$rowIndex])) {
  //     return response()->json(['error' => 'Invalid row index provided.'], 400);
  //   }

  //   $parsedData['data'][$rowIndex][$column] = $value;

  //   session(['parsedData' => $parsedData]);

  //   return response()->json(['success' => true]);
  // }

  // public function submitSingle(Request $request)
  // {
  //   $data = $request->all();
  //   $parsedData = session('parsedData');

  //   // Find row index from _row_index
  //   $rowIndex = $data['_row_index'] ?? null;

  //   if (is_null($rowIndex) || !isset($parsedData['data'][$rowIndex])) {
  //     return response()->json(['message' => 'Row not found in session.'], 404);
  //   }

  //   // Process DB insert/update here...
  //   UserNIK::updateOrCreate(
  //     ['periode_id' => $parsedData['periode_id'], 'user_code' => $data['user_code']],
  //     [
  //       'group' => $data['group'],
  //       'user_type' => "NIK",
  //       'license_type' => $data['license_type'],
  //       'last_login' => $data['last_login'],
  //       'valid_from' => $data['valid_from'],
  //       'valid_to' => $data['valid_to'],
  //     ]
  //   );

  //   // Remove this row from session data
  //   unset($parsedData['data'][$rowIndex]);
  //   $parsedData['data'] = array_values($parsedData['data']); // Reindex

  //   session()->put('parsedData', $parsedData);

  //   return response()->json(['message' => 'Row submitted successfully']);
  // }



  /**
   * Submits all rows of data to the User NIK table.
   *
   * Returns a StreamedResponse object which sends a JSON response with a 'progress' key
   * whose value increments from 0 to 100 as each row is processed.
   *
   * @param Request $request
   * @return \Illuminate\Http\StreamedResponse
   */

  public function submitAll(Request $request)
  {
    $parsedData = session('parsedData');

    if (!$parsedData || !isset($parsedData['data'])) {
      return response()->json(['error' => 'No data available for import.'], 400);
    }

    $periodeId = $parsedData['periode_id'];
    $dataArray = $parsedData['data'];
    $totalRows = count($dataArray);

    try {
      $response = new StreamedResponse(function () use ($dataArray, $periodeId, $totalRows) {
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
        $lastUpdate = microtime(true);
        $send(['progress' => 0]);

        foreach ($dataArray as $row) {
          try {
            $flagged = false;
            $keterangan = null;

            $rowErrors = $row['row_errors'] ?? [];
            $cellErrors = $row['cell_errors'] ?? [];

            if ((is_array($rowErrors) && count($rowErrors) > 0) || (is_array($cellErrors) && count($cellErrors) > 0)) {
              $flagged = true;
              $keterangan = 'Row errors: ' . json_encode($rowErrors) . '; Cell errors: ' . json_encode($cellErrors);
            }

            UserNIK::updateOrCreate(
              ['periode_id' => $periodeId, 'user_code' => $row['user_code']],
              [
                'group' => $row['group'],
                'user_type' => "NIK",
                'license_type' => $row['license_type'],
                'last_login' => $row['last_login'],
                'valid_from' => $row['valid_from'],
                'valid_to' => $row['valid_to'],
                'flagged' => $flagged,
                'keterangan' => $keterangan,
              ]
            );
          } catch (\Exception $e) {
            Log::error('Row import failed', ['row' => $row, 'error' => $e->getMessage()]);
          }

          $processed++;
          if (microtime(true) - $lastUpdate >= 1 || $processed === $totalRows) {
            $send(['progress' => (int) round($processed / max(1, $totalRows) * 100)]);
            $lastUpdate = microtime(true);
          }
        }

        $send(['success' => true, 'message' => 'Import complete']);
      });

      // Clear session after preparing stream
      session()->forget('parsedData');

      $response->headers->set('Content-Type', 'text/event-stream');
      $response->headers->set('Cache-Control', 'no-cache, no-transform');
      $response->headers->set('X-Accel-Buffering', 'no');
      $response->headers->set('Connection', 'keep-alive');

      return $response;
    } catch (\Exception $e) {
      Log::error('Error during bulk submit', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
      ]);
      return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
    }
  }
}
