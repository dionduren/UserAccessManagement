<?php

namespace App\Imports;

use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CompanyKompartemenPreviewImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Convert row to array and ensure all required fields exist
            $row = collect([
                'company' => $row['company'] ?? '',
                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                'kompartemen' => $row['kompartemen'] ?? null,
                'departemen_id' => $row['departemen_id'] ?? null,
                'departemen' => $row['departemen'] ?? null,
                'job_function' => $row['job_function'] ?? '',
                'composite_role' => $this->cleanCompositeRole($row['composite_role'] ?? ''),
            ]);

            // Add validation status
            $validationResult = $this->validateRow(
                $row['kompartemen_id'] ?? null,
                $row['kompartemen'] ?? null,
                $row['departemen_id'] ?? null,
                $row['departemen'] ?? null
            );

            $row['status'] = $validationResult;

            $this->rows->push($row);
        }
    }

    private function cleanCompositeRole($value): string
    {
        // Remove ALL whitespace characters (spaces, tabs, newlines)
        return preg_replace('/\s+/', '', (string)$value);
    }

    private function validateRow($kompartemenId, $kompartemenName, $departemenId, $departemenName)
    {
        if (!empty($kompartemenName) && empty($kompartemenId)) {
            return [
                'type' => 'warning',
                'message' => 'Kompartemen name exists but ID is missing'
            ];
        }

        if (!empty($kompartemenId)) {
            $kompartemenExists = Kompartemen::find($kompartemenId);
            if (!$kompartemenExists) {
                return [
                    'type' => 'error',
                    'message' => 'Invalid Kompartemen ID'
                ];
            }
        }

        if (!empty($departemenName) && empty($departemenId)) {
            return [
                'type' => 'warning',
                'message' => 'Departemen name exists but ID is missing'
            ];
        }

        if (!empty($departemenId)) {
            $departemenExists = Departemen::find($departemenId);
            if (!$departemenExists) {
                return [
                    'type' => 'error',
                    'message' => 'Invalid Departemen ID'
                ];
            }
        }

        return [
            'type' => 'valid',
            'message' => ''
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
