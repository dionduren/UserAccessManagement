<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DynamicUploadService
{
    public function handle(array $config, array $row): array
    {
        $row = $this->normalizeKeys($row, $config);
        $row = $this->resolveCompositeDateTime($row, $config);
        $row = $this->validateRequired($row, $config);
        $row = $this->parseDateTimes($row, $config);
        $row = $this->resolveLookups($row, $config);
        $payload = $this->buildPayload($row, $config);
        $where = $this->buildWhere($row, $config);

        if ($config['table'] === 'ms_user_detail') {
            if (!empty($row['_cell_errors']) || !empty($row['_cell_warnings'])) {
                $row['flagged'] = true;
                if (empty($row['keterangan'])) {
                    $row['keterangan'] = 'Terdapat error atau warning. Mohon periksa.';
                }
            }

            // Inject these into payload explicitly
            $payload = array_merge($payload, [
                'error_kompartemen_id' => $row['error_kompartemen_id'] ?? null,
                'error_kompartemen_name' => $row['error_kompartemen_id_name'] ?? null,
                'error_departemen_id' => $row['error_departemen_id'] ?? null,
                'error_departemen_name' => $row['error_departemen_id_name'] ?? null,
                'flagged' => $row['flagged'] ?? false,
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }

        $row['_row_issues_count'] = count(($row['_row_errors'] ?? [])) + count(($row['_cell_warnings'] ?? []));
        return [$payload, $where, $row['_row_errors'], [], $row['_cell_errors'], [], $row];
    }

    private function normalizeKeys(array $row, array $config): array
    {
        foreach ($config['columns'] as $key => $meta) {
            if (isset($meta['alias']) && isset($row[$meta['alias']])) {
                $row[$key] = $row[$meta['alias']];
            }
        }
        return $row;
    }

    private function validateRequired(array $row, array $config): array
    {
        $errors = [];

        foreach ($config['validate_columns'] ?? [] as $field) {
            // Skip validation here if this is a lookup field
            $isLookup = $config['columns'][$field]['type'] ?? null;
            if ($isLookup === 'lookup') continue;

            if (empty($row[$field])) {
                $errors[$field] = "$field is required";
            }
        }

        $row['_cell_errors'] = $errors;
        $row['_row_errors'] = array_values($errors);
        return $row;
    }

    private function parseDateTimes(array $row, array $config): array
    {
        foreach ($config['columns'] as $key => $meta) {
            if (in_array($meta['type'], ['date', 'datetime']) && !empty($row[$key])) {
                $raw = $row[$key];

                // Normalize input: trim strings
                if (is_string($raw)) {
                    $raw = trim($raw);
                }

                // Guard against known bad inputs
                if (in_array($raw, ['0', '0.0', '00:00:00', '-', '--', '0000-00-00', '', null], true)) {
                    $row[$key] = null;
                    continue;
                }

                try {
                    if (is_numeric($raw)) {
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$raw);
                    } else {
                        $date = \Carbon\Carbon::parse($raw);
                    }

                    $row[$key] = $meta['type'] === 'datetime'
                        ? $date->format('Y-m-d H:i:s')
                        : $date->format('Y-m-d');

                    $row["{$key}_display"] = $meta['type'] === 'datetime'
                        ? \Carbon\Carbon::parse($row[$key])->locale('id_ID')->translatedFormat('Y F d - H:i:s')
                        : \Carbon\Carbon::parse($row[$key])->locale('id_ID')->translatedFormat('Y F d');
                } catch (\Exception $e) {
                    $row[$key] = null;
                }
            }
        }

        return $row;
    }

    private function resolveLookups(array $row, array $config): array
    {
        $notes = [];

        foreach ($config['columns'] as $key => $meta) {
            if (($meta['type'] ?? null) === 'lookup') {
                $value = $row[$key] ?? null;
                $model = $meta['model'];
                $idField = $meta['id_field'] ?? 'id';

                if (empty($value)) {
                    $row['_cell_warnings'][$key] = "$key is empty (optional)";
                    $notes[] = "- warning $key = ID " . ucfirst(str_replace('_id', '', $key)) . " kosong, mohon cek kembali";
                } else {
                    $record = $model::where($idField, $value)->first();

                    if (!$record) {
                        $row['_cell_errors'][$key] = "$key value not found in master";
                        $row['_row_errors'][] = "$key value not found";
                        $row["error_{$key}"] = $value;
                        $row["error_{$key}_name"] = '(not found)';
                        $row['flagged'] = true;
                        $notes[] = "- error $key = ID " . ucfirst(str_replace('_id', '', $key)) . " tidak ada di dalam Master Data";
                    } elseif ($key == "job_role") {
                        $row[$key] = $record->job_role_id;
                    } else {
                        if ($idField == "shortname") {
                            $row[$key] = $record->company_code; // override company = A000
                            $row["{$key}_nama"] = $record->nama; // optional, for preview
                        } elseif ($key == "job_role") {
                            // $row[$key] = $record->job_role_id;
                        } else {
                            $row["{$key}_nama"] = $record->nama;
                        }
                    }
                }
            }
        }

        // Final assign
        if ($config['table'] === 'ms_user_detail' && count($notes)) {
            $row['flagged'] = true;
            $row['keterangan'] = implode("\n", $notes); // fix: real newline!
        }


        return $row;
    }

    private function buildPayload(array $row, array $config): array
    {
        $payload = [];
        foreach ($config['columns'] as $key => $meta) {
            if (!($meta['custom'] ?? false)) {
                $dbField = $meta['db_field'] ?? $key;
                $payload[$dbField] = $row[$key] ?? null;
            }
        }
        $payload['created_by'] = auth()->user()->name;
        return $payload;
    }

    private function buildWhere(array $row, array $config): array
    {
        $where = [];

        foreach ($config['where_fields'] as $field) {
            $where[$field] = $row[$field] ?? null;
        }

        // Dynamically add periode_id if config says so
        if (($config['uses_periode'] ?? false) === true) {
            $where['periode_id'] = $row['periode_id'] ?? null;
        }

        return $where;
    }

    public function generateTabulatorColumns(array $config): array
    {
        $columns = [];

        foreach ($config['columns'] as $key => $meta) {
            $type = $meta['type'] ?? 'string';

            // 📌 Hide raw field if it's date/datetime
            if (in_array($type, ['date', 'datetime'])) {
                // Raw value (used for sorting & searching)
                $columns[] = [
                    'field' => $key,
                    'visible' => false,
                    'headerSort' => true,
                    'headerFilter' => 'input',
                ];

                // Display column, but sort/search using raw field
                $columns[] = [
                    'title' => $meta['header_name'] ?? ucfirst(str_replace('_', ' ', $key)),
                    'field' => "{$key}_display",
                    'hozAlign' => 'center',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'sorter' => (object)[
                        'targetField' => $key // 👈 this tells Tabulator to sort using raw date
                    ],
                ];

                continue;
            }

            // Standard column (string, etc)
            $columns[] = [
                'title' => $meta['header_name'] ?? ucfirst(str_replace('_', ' ', $key)),
                'field' => $key,
                'hozAlign' => 'center',
                'headerFilter' => 'input'
            ];

            // Lookup support
            if ($type === 'lookup') {
                $columns[] = [
                    'title' => ucfirst(str_replace('_id', '', $key)),
                    'field' => "{$key}_nama",
                    'headerSort' => false,
                ];
            }
        }

        return $columns;
    }


    public function submitRow(array $config, array $payload, array $where): bool
    {
        $modelClass = $config['model'];

        foreach (['valid_from', 'valid_to', 'last_login'] as $key) {
            if (isset($payload[$key]) && ($payload[$key] === '0' || $payload[$key] === 0)) {
                $payload[$key] = null;
            }
        }

        $modelClass::updateOrCreate($where, $payload);
        return true;
    }

    private function resolveCompositeDateTime(array $row, array $config): array
    {
        if (!isset($config['composite_datetime'])) return $row;

        foreach ($config['composite_datetime'] as $targetField => $sources) {
            if (count($sources) !== 2) continue;
            [$dateField, $timeField] = $sources;

            $date = $row[$dateField] ?? null;
            $time = $row[$timeField] ?? null;

            if ($date && $time) {
                try {
                    $combined = \Carbon\Carbon::createFromFormat('d.m.Y H:i:s', "$date " . gmdate("H:i:s", round(86400 * $time)));
                    $row[$targetField] = $combined->format('Y-m-d H:i:s');
                    $row["{$targetField}_display"] = $combined->locale('id_ID')->translatedFormat('Y F d - H:i:s');
                } catch (\Exception $e) {
                    $row[$targetField] = null;
                    $row["{$targetField}_display"] = null;
                }
            }
        }

        return $row;
    }
}
