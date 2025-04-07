<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Periode;
use Illuminate\Http\Request;
use App\Models\TempUploadSession;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DynamicUploadController extends Controller
{

    public function upload($module)
    {
        $modules = config('dynamic_uploads.modules');
        abort_unless(array_key_exists($module, $modules), 404);

        $periodes = Periode::select('id', 'definisi')->get();

        return view('dynamic_upload.upload', compact('module', 'periodes'));
    }

    public function handleUpload(Request $request, $module)
    {
        $modules = config('dynamic_uploads.modules');
        abort_unless(array_key_exists($module, $modules), 404);

        $request->validate([
            'periode_id' => 'required|numeric',
            'excel_file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('excel_file');
        $rows = Excel::toArray([], $file)[0] ?? [];

        // 🔵 Convert header row to associative rows:
        $header = array_shift($rows);
        $columns = $modules[$module]['columns'];

        $dateFields = array_keys(array_filter($columns, fn($meta) => $meta['type'] === 'date'));

        $excelToDate = fn($value) =>
        is_numeric($value)
            ? Date::excelToDateTimeObject($value)->format('Y-m-d')
            : Carbon::parse(str_replace(['.', '/'], '-', $value))->format('Y-m-d');

        $parsedRows = [];


        foreach ($rows as $row) {
            $assocRow = array_combine($header, $row);

            // 🟢 Handle last_login only for terminated_employee
            if ($module === 'terminated_employee') {
                if (isset($assocRow['terminate_date']) && !isset($assocRow['tanggal_resign'])) {
                    $assocRow['tanggal_resign'] = $assocRow['terminate_date'];
                }

                try {
                    if (!empty($assocRow['last_login_date'])) {
                        $assocRow['last_login_date'] = Carbon::parse(str_replace(['.', '/'], '-', $assocRow['last_login_date']))->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $assocRow['last_login_date'] = null;
                }

                try {
                    if (!empty($assocRow['last_login_time'])) {
                        $assocRow['last_login_time'] = is_numeric($assocRow['last_login_time'])
                            ? Date::excelToDateTimeObject($assocRow['last_login_time'])->format('H:i:s')
                            : Carbon::parse($assocRow['last_login_time'])->format('H:i:s');
                    }
                } catch (\Exception $e) {
                    $assocRow['last_login_time'] = null;
                }

                foreach (['valid_from', 'valid_to'] as $field) {
                    if (in_array($assocRow[$field] ?? '', ['0', '00/01/1900'])) {
                        $assocRow[$field] = null;
                    }
                }
            }

            // 🔁 Convert other date fields
            foreach ($dateFields as $field) {
                if (!empty($assocRow[$field])) {
                    try {
                        $assocRow[$field] = $excelToDate($assocRow[$field]);
                    } catch (\Exception $e) {
                        $assocRow[$field] = null;
                    }
                }
            }

            $parsedRows[] = $assocRow;
        }

        TempUploadSession::create([
            'module' => $module,
            'periode_id' => $request->periode_id,
            'data' => $parsedRows,
            'columns' => $this->generateTabulatorColumns($columns),
        ]);

        return redirect()->route('dynamic_upload.preview', $module);
    }


    public function preview($module)
    {
        $modules = config('dynamic_uploads.modules');
        abort_unless(array_key_exists($module, $modules), 404);

        // $tabulatorColumns = $this->generateTabulatorColumns($modules[$module]['columns']);
        $columns = $this->generateTabulatorColumns($modules[$module]['columns']);

        return view('dynamic_upload.preview', compact('module', 'columns'));
    }

    public function getPreviewData($module)
    {
        $modules = config('dynamic_uploads.modules');
        abort_unless(array_key_exists($module, $modules), 404);

        $temp = TempUploadSession::where('module', $module)->latest()->first();
        $data = $temp?->data ?? [];
        $columns = $modules[$module]['columns'];

        $dataWithIds = [];
        foreach ($data as $index => $row) {
            $row['DT_RowId'] = 'row_' . $index;
            $row['_row_index'] = $index;

            $errors = $this->validateLookupValues($row, $columns);
            $row['_row_errors'] = $errors;
            $row['_row_errors_sort'] = count($errors); // 🟢 used for sorting errors on top

            $dataWithIds[] = $row;
        }

        return response()->json(['data' => $dataWithIds]);
    }

    public function updateInlineSession(Request $request, $module)
    {
        $temp = TempUploadSession::where('module', $module)->latest()->first();
        $data = $temp?->data ?? [];

        $rowIndex = (int) $request->input('row_index');
        $column = $request->input('column');
        $value = $request->input('value');

        if (!isset($data[$rowIndex])) {
            return response()->json(['error' => 'Row not found.'], 400);
        }

        // Log::info("Changed value of column '{$column}' in module '{$module}' at row #{$rowIndex} from '{$data[$rowIndex][$column]}' into '{$value}'");

        $data[$rowIndex][$column] = $value;
        $temp->update(['data' => $data]);


        return response()->json(['success' => true]);
    }

    public function submitAll(Request $request, $module)
    {
        $modules = config('dynamic_uploads.modules');
        abort_unless(array_key_exists($module, $modules), 404);

        $temp = TempUploadSession::where('module', $module)->latest()->first();
        $dataArray = $temp?->data ?? [];
        $periodeId = $temp?->periode_id ?? null;

        if (!$periodeId) {
            return response()->json(['error' => 'Missing periode_id in upload session'], 400);
        }

        $tableName = $modules[$module]['table'];
        $totalRows = count($dataArray);

        $userType = $modules[$module]['user_type']; // 💡 Now configurable

        // dd($tableName, $temp);

        return new StreamedResponse(function () use ($dataArray, $totalRows, $tableName, $modules, $module, $periodeId, $userType) {
            foreach ($dataArray as $index => $row) {
                $payload = [];

                if ($userType) {
                    $payload['user_type'] = $userType;
                }

                if (!in_array($tableName, ['ms_terminated_employee'])) {
                    $payload['periode_id'] = $periodeId;r
                }


                foreach ($modules[$module]['columns'] as $colName => $meta) {
                    $targetDbField = $meta['db_field'] ?? $colName;

                    if ($meta['type'] === 'lookup' && isset($meta['model'])) {
                        if (($module === 'user_nik' || $module === 'user_generic') && $colName === 'group') {

                            $payload[$targetDbField] = $row[$colName] ?? null;
                        } else {
                            // For other modules, still resolve to ID
                            $model = $meta['model'];
                            $field = $meta['field'];
                            $record = $model::where($field, $row[$colName])->first();
                            $payload[$targetDbField] = $record ? $record->id : null;
                        }
                    } else {
                        $payload[$targetDbField] = $row[$colName] ?? null;
                    }
                }

                // Log the payload with human-readable values
                // Log::info("Saving payload to {$tableName}:", $payload);

                // 🟢 Special case for `tr_nik_job_role`
                if ($tableName === 'tr_nik_job_role') {

                    // Log::info("Looking up job_role for NIK {$row['nik']}: " . json_encode($row['job_role']));

                    // Get JobRole ID from dropdown value (nama_jabatan)
                    $cleanName = trim(strtolower($row['job_role']));
                    $jobRole = \App\Models\JobRole::get()
                        ->firstWhere(fn($r) => strtolower(trim($r->nama_jabatan)) === $cleanName);
                    // if (!$jobRole) {
                    //     Log::warning("❌ Failed to find job_role_id for '{$row['job_role']}' (NIK {$row['nik']})");
                    // }

                    $payload['job_role_id'] = $jobRole ? $jobRole->id : null;

                    // REMOVE the original `job_role` text from payload!
                    unset($payload['job_role']);

                    \DB::table($tableName)->updateOrInsert(
                        [
                            'periode_id' => $periodeId,
                            'nik' => $row['nik'],
                            'job_role_id' => $payload['job_role_id'],
                        ],
                        $payload
                    );
                } elseif ($tableName === 'ms_user_detail') {
                    unset($payload['user_code']); // Remove user_code field from payload if not needed in this table

                    \DB::table($tableName)->updateOrInsert(
                        ['periode_id' => $periodeId, 'nik' => $row['user_code']], // map user_code → nik
                        $payload
                    );
                } elseif ($tableName === 'ms_terminated_employee') {
                    if ($module === 'terminated_employee') {
                        $datePart = $row['last_login_date'] ?? null;
                        $timePart = $row['last_login_time'] ?? '00:00:00';
                        try {
                            $merged = trim("{$datePart} {$timePart}");
                            $payload['last_login'] = Carbon::parse($merged)->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            $payload['last_login'] = null;
                        }
                        unset($payload['last_login_date'], $payload['last_login_time']);
                    }

                    \DB::table($tableName)->updateOrInsert(
                        ['nik' => $row['nik']], // map user_code → nik
                        $payload
                    );
                } else {
                    \DB::table($tableName)->updateOrInsert(
                        ['periode_id' => $periodeId, 'user_code' => $row['user_code']],
                        $payload
                    );
                }

                $progress = (($index + 1) / $totalRows) * 100;
                echo json_encode(['progress' => $progress]) . "\n";
                ob_flush();
                flush();
            }
        }, 200, ['Content-Type' => 'application/json']);
    }


    protected function generateTabulatorColumns(array $columns)
    {
        $tabulator = [];

        foreach ($columns as $field => $meta) {
            $column = [
                'title' => ucfirst(str_replace('_', ' ', $field)),
                'field' => $field,
                'hozAlign' => 'center',
            ];

            // ✅ Special case for Job Role dropdown
            if ($field === 'job_role' || $field === 'nama_jabatan') {
                $jobRoles = \App\Models\JobRole::pluck('nama_jabatan')->toArray();
                $column['editor'] = 'list';
                $column['editorParams'] = [
                    'values' => $jobRoles,
                    'autocomplete' => true,
                    'clearable' => true
                ];
                $column['headerFilter'] = 'list';
                $column['headerFilterParams'] = ['values' => $jobRoles];
            } else if ($meta['type'] === 'lookup' && isset($meta['model'], $meta['field'])) {
                $model = $meta['model'];
                $fieldLookup = $meta['field'];

                // Dynamically generate dropdown options from DB
                $options = $model::select($fieldLookup)->pluck($fieldLookup)->toArray();

                $column['editor'] = 'list';
                $column['editorParams'] = [
                    'values' => $options,
                    'autocomplete' => true,
                    'clearable' => true
                ];
                $column['headerFilter'] = 'list';
                $column['headerFilterParams'] = [
                    'values' => $options,
                    'autocomplete' => true,
                    'clearable' => true
                ];
            } else if ($meta['type'] === 'date') {
                $column['editor'] = 'date';
                $column['editorParams'] = [
                    'format' => "yyyy-MM-dd",
                ];

                $column['headerFilter'] = 'input';
                $column['headerFilterPlaceholder'] = 'YYYY-MM-DD';
            } else {
                $column['editor'] = 'input';
                $column['headerFilter'] = 'input';
            }

            $tabulator[] = $column;
        }

        return $tabulator;
    }

    protected function validateLookupValues(array $row, array $columns): array
    {
        $errors = [];

        foreach ($columns as $col => $meta) {
            if ($meta['type'] === 'lookup') {
                $model = $meta['model'];
                $field = $meta['field'];

                if (!empty($row[$col])) {
                    $exists = $model::where($field, $row[$col])->exists();

                    if (!$exists) {
                        $errors[] = "Invalid {$col}: '{$row[$col]}'";
                    }
                }
            }
        }

        return $errors;
    }
}
