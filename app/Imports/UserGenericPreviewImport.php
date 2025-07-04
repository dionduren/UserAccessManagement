<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UserGenericPreviewImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Map custom Excel column names to expected keys
            $mappedRow = [
                'group' => $row['group'] ?? null,
                'user_code' => $row['user_code'] ?? null,
                'user_type' => $row['user_type'] ?? null,
                'cost_code' => $row['cost_code'] ?? null,
                'license_type' => $row['license_type'] ?? null,
                'last_login' => $row['last_login'] ?? null,
                'valid_from' => $row['valid_from'] ?? null,
                'valid_to' => $row['valid_to'] ?? null,
            ];

            $this->rows->push(collect($mappedRow));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
