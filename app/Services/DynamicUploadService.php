<?php

namespace App\Services;

use App\Models\Company;
use App\Models\userNIK;

use App\Models\Departemen;
use App\Models\UserDetail;
use App\Models\Kompartemen;
use App\Models\userGeneric;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            // Only validate if in validate_columns list
            if (in_array($key, $validateColumns) && empty($value)) {
                $warnings[] = "$dbField is required but empty";
                $cellWarnings[$dbField] = "$dbField is required but empty";
            }

            try {
                if ($meta['type'] === 'lookup' && $value) {
                    $model = $meta['model'];
                    $idField = $meta['id_field'] ?? 'id';  // NEW: allow override in config

                    $record = $model::where($idField, $value)->first();

                    if ($record) {
                        $payload[$key] = $record->$idField;  // keep the ID for DB
                        $assocRow["{$key}_nama"] = $record->nama;  // attach name for display
                    } else {
                        $payload[$key] = null;
                        $assocRow["{$key}_nama"] = '(not found)';
                        if (in_array($key, $validateColumns)) {
                            $cellErrors[$key] = "$key not found in database";
                            $hardErrors[] = "$key $value not found";
                        }
                    }
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
        } else {
            foreach ($moduleConfig['columns'] as $key => $meta) {
                $dbField = $meta['db_field'] ?? $key;
                $payload[$dbField] = $row[$key] ?? '';
            }
        }


        $payload['created_by'] = auth()->user()->name;

        return $payload;
    }


    public function generateTabulatorColumns(array $config): array
    {
        $columns = [];

        foreach ($config['columns'] as $key => $meta) {
            if ($key === 'periode_id') continue;

            if ($key === 'nik') {
                $col = [
                    'title' => 'NIK',
                    'field' => 'user_code',
                    'hozAlign' => 'start',
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
}
