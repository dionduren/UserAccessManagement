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

    // public function collection(Collection $rows)
    // {
    //     $this->rows = $this->rows->merge($rows);
    // }

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
                'composite_role' => $row['composite_role'] ?? '',
            ]);

            // Add validation status directly to the array
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

    private function validateRow($kompartemenId, $kompartemenName, $departemenId, $departemenName)
    {
        // Check kompartemen validation
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

        // Check departemen validation
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

    // private function validateRow($kompartemenId, $departemenId)
    // {
    //     $status = [
    //         'type' => 'valid',
    //         'message' => ''
    //     ];

    //     // Check if IDs exist in database when provided
    //     if ($kompartemenId !== null) {
    //         $kompartemenExists = Kompartemen::find($kompartemenId);
    //         if (!$kompartemenExists) {
    //             return [
    //                 'type' => 'error',
    //                 'message' => 'Invalid Kompartemen ID'
    //             ];
    //         }
    //     }

    //     if ($departemenId !== null) {
    //         $departemenExists = Departemen::find($departemenId);
    //         if (!$departemenExists) {
    //             return [
    //                 'type' => 'error',
    //                 'message' => 'Invalid Departemen ID'
    //             ];
    //         }
    //     }

    //     return $status;
    // }

    public function chunkSize(): int
    {
        return 1000;
    }
}
