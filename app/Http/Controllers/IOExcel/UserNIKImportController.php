<?php

namespace App\Http\Controllers\IOExcel;

use App\Http\Controllers\Controller;
use App\Models\UserNIK;
use App\Imports\UserNIKImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Maatwebsite\Excel\Facades\Excel;
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

      return redirect()->route('user-nik.preview');
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
    $data = session('parsedData');

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
          'group' => 'required',
          'user_code' => 'required',
          'user_type' => 'required',
          'license_type' => 'required',
          'last_login' => 'required|date',
          'valid_from' => 'required|date',
          'valid_to' => 'required|date'
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
            'group' => $row['group'],
            'user_code' => $row['user_code'],
            'user_type' => $row['user_type'],
            'license_type' => $row['license_type'],
            'last_login' => $row['last_login'],
            'valid_from' => $row['valid_from'],
            'valid_to' => $row['valid_to'],
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


      return view('master-data.user_nik.preview', ['parsedData' => $previewData]);
    } catch (\Exception $e) {
      Log::error('Error during Preview', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      // Temporary Debugging only:
      return redirect()->back()->with('error', 'Error during Preview: ' . $e->getMessage());
    }
  }


  /**
   * Get the data for the preview table
   */
  public function getPreviewData()
  {
    $parsedData = session('parsedData');

    // \dd($parsedData);

    if (!$parsedData || !isset($parsedData['data'])) {
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

  public function updateInlineSession(Request $request)
  {
    $rowIndex = (int) $request->input('row_index');
    $column = $request->input('column');
    $value = $request->input('value');

    $parsedData = session('parsedData');

    if (!isset($parsedData['data'][$rowIndex])) {
      return response()->json(['error' => 'Invalid row index provided.'], 400);
    }

    $parsedData['data'][$rowIndex][$column] = $value;

    session(['parsedData' => $parsedData]);

    return response()->json(['success' => true]);
  }


  public function confirmImport(Request $request)
  {
    $parsedData = session('parsedData');

    if (!$parsedData || !isset($parsedData['data'])) {
      return response()->json(['error' => 'No data available for import.'], 400);
    }

    $periodeId = $parsedData['periode_id'];
    $dataArray = $parsedData['data'];
    $totalRows = count($dataArray);

    return new StreamedResponse(function () use ($dataArray, $periodeId, $totalRows) {
      foreach ($dataArray as $index => $row) {
        UserNIK::updateOrCreate(
          ['periode_id' => $periodeId, 'user_code' => $row['user_code']],
          [
            'group' => $row['group'],
            'user_type' => $row['user_type'],
            'license_type' => $row['license_type'],
            'last_login' => $row['last_login'],
            'valid_from' => $row['valid_from'],
            'valid_to' => $row['valid_to'],
          ]
        );

        $progress = (($index + 1) / $totalRows) * 100;
        echo json_encode(['progress' => $progress]) . "\n";
        ob_flush();
        flush();
        sleep(1); // simulate processing delay
      }
    }, 200, ['Content-Type' => 'application/json']);
  }
}
