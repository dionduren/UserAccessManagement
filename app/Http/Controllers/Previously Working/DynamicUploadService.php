<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\userNIK;
use App\Models\UserDetail;
use App\Models\userGeneric;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DynamicUploadService
{

    public function processRow(array $config, array $assocRow): array
    {
        $payload = [];
        $where = [];
        $hardErrors = [];
        $warnings = [];
        $cellErrors = [];
        $cellWarnings = [];

        $validateColumns = $config['validate_columns'] ?? [];  // NEW: configurable

        foreach ($config['columns'] as $key => $meta) {
            $excelKey = $meta['excel_column'] ?? $key;
            $dbField = $meta['db_field'] ?? $key;
            $value = $assocRow[$excelKey] ?? null;

            // Handle required fields for terminated_employee
            if ($config['table'] === 'ms_terminated_employee') {
                $requiredFields = ['nik', 'nama', 'tanggal_resign'];
                foreach ($requiredFields as $field) {
                    if (empty($assocRow[$field])) {
                        $hardErrors[] = "$field is required";
                        $cellErrors[$field] = "Field is required";
                    }
                }

                // Handle last_login composite datetime
                if (isset($assocRow['last_login_date']) && isset($assocRow['last_login_time'])) {
                    try {
                        $dateVal = $assocRow['last_login_date'];
                        $timeVal = $assocRow['last_login_time'];

                        if (is_numeric($dateVal)) {
                            $date = Date::excelToDateTimeObject($dateVal)
                                ->format('Y-m-d');
                        } else {
                            $date = Carbon::createFromFormat('d.m.Y', $dateVal)->format('Y-m-d');
                        }

                        if (is_numeric($timeVal)) {
                            $seconds = round($timeVal * 86400);
                            $timeFormatted = sprintf(
                                '%02d:%02d:%02d',
                                floor($seconds / 3600),
                                floor(($seconds % 3600) / 60),
                                $seconds % 60
                            );
                            $assocRow['last_login'] = $date . ' ' . $timeFormatted;
                        }
                    } catch (\Exception $e) {
                        $assocRow['last_login'] = null;
                    }
                }

                // Handle date fields
                // $dateFields = ['tanggal_resign', 'valid_from', 'valid_to'];
                // foreach ($dateFields as $field) {
                //     if (!empty($assocRow[$field])) {
                //         try {
                //             if (is_numeric($assocRow[$field])) {
                //                 $date = Date::excelToDateTimeObject($assocRow[$field]);
                //                 $assocRow[$field] = $date->format('Y-m-d');
                //             } else {
                //                 $assocRow[$field] = Carbon::createFromFormat('d.m.Y', $assocRow[$field])->format('Y-m-d');
                //             }
                //         } catch (\Exception $e) {
                //             $warnings[] = "Invalid date format for $field";
                //             $cellWarnings[$field] = "Invalid date format";
                //         }
                //     }
                // }

                // Convert Excel dates to proper format
                $dateFields = ['tanggal_resign', 'valid_from', 'valid_to'];
                foreach ($dateFields as $field) {
                    if (isset($assocRow[$field]) && is_numeric($assocRow[$field])) {
                        try {
                            $date = Date::excelToDateTimeObject($assocRow[$field]);
                            $assocRow[$field] = $date->format('Y-m-d');
                        } catch (\Exception $e) {
                            $assocRow[$field] = null;
                        }
                    }
                }
            }

            // Only validate if in validate_columns list
            if (in_array($key, $validateColumns) && empty($value)) {
                $warnings[] = "$dbField is required but empty";
                $cellWarnings[$dbField] = "$dbField is required but empty";
            }

            try {
                if ($meta['type'] === 'lookup' && $value) {
                    $model = $meta['model'];
                    $idField = $meta['id_field'] ?? 'id';
                    // Log::info('$idField awal = ' . $idField); // NEW: allow override in config

                    // Log::info('Lookup values:', [
                    //     'model' => $model,
                    //     'idField' => $idField,
                    //     'value' => $value
                    // ]);
                    $record = $model::where($idField, $value)->first();
                    // Log::info('$record awal = ' . $record);

                    if ($record) {
                        $payload[$key] = $record->$idField;  // keep the ID for DB
                        if ($idField === 'nama' && $model == JobRole::class) {
                            // Log::info('$idField = ' . $idField);
                            $assocRow["{$key}_id"] = $record->job_role_id;
                        } else {
                            $assocRow["{$key}_nama"] = $record->nama;  // attach name for display
                        }
                    } else {
                        $payload[$key] = null;
                        $assocRow["{$key}_nama"] = '(not found)';
                        if (in_array($key, $validateColumns)) {
                            $cellErrors[$key] = "$key not found in database";
                            $hardErrors[] = "$key $value not found";
                        }
                    }
                } elseif (in_array($meta['type'], ['date', 'datetime']) && is_numeric($value)) {
                    $base = \Carbon\Carbon::create(1899, 12, 30);
                    $converted = $base->copy()->addDays(floor($value));
                    if ($meta['type'] === 'datetime') {
                        $timePart = $value - floor($value);
                        $converted->addSeconds(round($timePart * 86400));
                        $value = $converted->format('Y-m-d H:i:s');
                    } else {
                        $value = $converted->format('Y-m-d');
                    }
                    $payload[$key] = $value;
                    $assocRow[$key] = $value; // display on preview table
                } else {
                    $payload[$key] = $value;
                }
            } catch (\Exception $e) {
                $hardErrors[] = "Error in $key: " . $e->getMessage();
                $cellErrors[$key] = $e->getMessage();
            }
        }

        foreach ($config['where_fields'] as $field) {
            $where[$field] = $payload[$field] ?? null;
        }

        return [$payload, $where, $hardErrors, $warnings, $cellErrors, $cellWarnings, $assocRow];
    }

    public function transformPayload(array $moduleConfig, array $row): array
    {
        $payload = [];

        if ($moduleConfig['model'] === UserDetail::class) {
            $payload = [
                'nik' => $row['user_code'] ?? '',
                'nama' => $row['nama'] ?? '',
                'email' => $row['email'] ?? '',
                'company_id' => Company::where('shortname', $row['company'] ?? '')->value('company_code'),
                'direktorat' => $row['direktorat'] ?? '',
                'kompartemen_id' => $row['kompartemen_id'] ?? '',
                'departemen_id' => $row['departemen_id'] ?? '',
                'periode_id' => $row['periode_id'] ?? '',
            ];
        } elseif ($moduleConfig['model'] === userNIK::class || $moduleConfig['model'] === userGeneric::class) {
            foreach ($moduleConfig['columns'] as $key => $meta) {
                $dbField = $meta['db_field'] ?? $key;
                $value = $row[$key] ?? '';

                if (in_array($key, ['valid_from', 'valid_to'])) {
                    if (!empty($value)) {
                        $value = Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
                    } else {
                        $value = null;  // ✅ explicitly set null for empty
                    }
                }

                $payload[$dbField] = $value;
            }
        } elseif ($moduleConfig['table'] === 'ms_terminated_employee') {
            // First handle the date fields
            $dateFields = ['tanggal_resign', 'valid_from', 'valid_to'];
            foreach ($dateFields as $field) {
                $row[$field] = $this->formatDateField($row[$field] ?? null);
            }

            // Handle last_login composite datetime
            $dateVal = $row['last_login_date'] ?? null;
            $timeVal = $row['last_login_time'] ?? null;

            $lastLogin = null;
            if ($dateVal && is_numeric($timeVal)) {
                try {
                    // Convert time decimal to H:i:s
                    $seconds = round($timeVal * 86400);
                    $timeFormatted = sprintf(
                        '%02d:%02d:%02d',
                        floor($seconds / 3600),
                        floor(($seconds % 3600) / 60),
                        $seconds % 60
                    );

                    // Format the date
                    $date = $this->formatDateField($dateVal);
                    if ($date) {
                        $lastLogin = $date . ' ' . $timeFormatted;
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to parse last_login", [
                        'date' => $dateVal,
                        'time' => $timeVal
                    ]);
                }
            }

            // Special handling for valid_to = 0
            if (isset($row['valid_to']) && ($row['valid_to'] === 0 || $row['valid_to'] === '0')) {
                $row['valid_to'] = null;
            }

            $payload = [
                'nik' => $row['nik'] ?? '',
                'nama' => $row['nama'] ?? '',
                'tanggal_resign' => $row['tanggal_resign'],
                'status' => $row['status'] ?? '',
                'last_login' => $lastLogin,
                'valid_from' => $row['valid_from'],
                'valid_to' => $row['valid_to'],
                'periode_id' => $row['periode_id'] ?? '',
                'created_by' => auth()->user()->name,
                'departemen_id' => null,
                'kompartemen_id' => null,
            ];

            return $payload;
        } else {
            foreach ($moduleConfig['columns'] as $key => $meta) {
                $dbField = $meta['db_field'] ?? $key;
                $value = $row[$key] ?? '';

                if (in_array($meta['type'], ['date', 'datetime']) && $value) {
                    $value = $this->formatDateTimeField($value, $meta['type'] === 'datetime');
                    $payload[$key] = $value;
                    $assocRow[$key] = $value; // display on preview table
                } else {
                    $payload[$key] = $value;
                }

                $payload[$dbField] = $value;
            }

            $payload['created_by'] = auth()->user()->name;

            return $payload;
        }

        // Handle composite datetime fields like: ['last_login' => ['last_login_date', 'last_login_time']]
        // if (isset($moduleConfig['composite_datetime']) && is_array($moduleConfig['composite_datetime'])) {
        //     foreach ($moduleConfig['composite_datetime'] as $field => [$dateKey, $timeKey]) {
        //         $dateVal = $row[$dateKey] ?? null;
        //         $timeVal = $row[$timeKey] ?? null;

        //         if (is_numeric($dateVal) && is_numeric($timeVal)) {
        //             $base = \Carbon\Carbon::create(1899, 12, 30)->addDays(floor($dateVal));
        //             $base->addSeconds(round(($timeVal - floor($timeVal)) * 86400));
        //             $payload[$field] = $base->format('Y-m-d H:i:s');
        //         } else {
        //             $payload[$field] = null;
        //         }
        //     }
        // }


        $payload['created_by'] = auth()->user()->name;

        return $payload;
    }


    public function generateTabulatorColumns(array $config): array
    {
        $columns = [];

        foreach ($config['columns'] as $key => $meta) {
            Log::info("Column Key = " . $key);

            if ($key === 'periode_id') continue;

            if ($key === 'nik') {
                $col = [
                    'title' => 'NIK',
                    'field' => $meta['field'] ?? ($meta['is_nik'] ? 'nik' : 'user_code'), // Dynamic field name
                    'hozAlign' => 'start',
                    'editor' => $meta['type'] === 'lookup' ? 'list' : 'input',
                    'headerFilter' => 'input',
                ];
            } elseif ($key === 'job_role_id') {
                $col = [
                    'title' => 'Job Role ID',
                    'field' => 'job_role_id',
                    'hozAlign' => 'center',
                    'editor' => $meta['type'] === 'lookup' ? 'list' : 'input',
                    'headerFilter' => 'input',
                ];
            } else {
                $col = [
                    'title' => $meta['header_name'] ?? ucfirst(str_replace('_', ' ', $key)),
                    'field' => $key,
                    'hozAlign' => 'center',
                    'editor' => $meta['type'] === 'lookup' ? 'list' : 'input',
                    'headerFilter' => 'input',
                ];
            }

            if ($meta['type'] === 'lookup') {
                $model = $meta['model'];
                if ($key === 'kompartemen_id') {
                    $values = $model::pluck('nama', 'kompartemen_id')->map(function ($name, $id) {
                        return ['value' => $id, 'label' => $name];
                    })->values();
                } elseif ($key === 'departemen_id') {
                    $values = $model::pluck('nama', 'departemen_id')->map(function ($name, $id) {
                        return ['value' => $id, 'label' => $name];
                    })->values();
                } else {
                    $values = $model::pluck('nama', 'id')->map(function ($name, $id) {
                        return ['value' => $id, 'label' => $name];
                    })->values();
                }

                $col['editorParams'] = ['values' => $values, 'autocomplete' => true, 'clearable' => true];
                $col['headerFilter'] = 'list';
                $col['headerFilterParams'] = ['values' => $values];
            }

            $columns[] = $col;

            if ($meta['type'] === 'lookup') {
                $columns[] = [
                    'title' => ucfirst(str_replace('_id', '', $key)),
                    'field' => "{$key}_nama",
                    'headerSort' => false,
                ];
            }
        }

        return $columns;
    }

    // public function submitRow(array $moduleConfig, array $payload, array $where): bool
    // {
    //     $modelClass = $moduleConfig['model'];
    //     $payload['created_by'] = auth()->user()->name;
    //     $modelClass::updateOrCreate($where, $payload);
    //     return true;
    // }

    public function submitRow(array $moduleConfig, array $payload, array $where): bool
    {
        $modelClass = $moduleConfig['model'];
        $modelClass::updateOrCreate($where, $payload);
        return true;
    }

    protected function formatDateField($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }
            return Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Date parsing failed", ['value' => $value]);
            return null;
        }
    }
}
