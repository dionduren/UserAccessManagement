<?php

namespace App\Imports;

use App\Models\Tcode;
use App\Models\SingleRole;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TcodeSingleRoleImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // Clean targeted fields (remove all whitespace characters)
        $singleRoleName = $this->cleanValue($row['single_role'] ?? '');
        $tcodeCode = $this->cleanValue($row['tcode'] ?? '');

        if ($singleRoleName !== '') {
            $singleRole = SingleRole::firstOrCreate([
                'nama' => $singleRoleName,
                'deskripsi' => $row['single_role_desc'] ?? null,
            ]);
        }

        if ($tcodeCode !== '') {
            $tcode = Tcode::firstOrCreate([
                'code' => $tcodeCode,
                'sap_module' => $row['sap_module'] ?? null,
                'deskripsi' => $row['tcode_desc'] ?? null,
            ]);
        }

        // Relation logic can be added here if needed
    }

    private function cleanValue(string $value): string
    {
        // Remove ALL whitespace characters
        return preg_replace('/\s+/', '', $value);
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
