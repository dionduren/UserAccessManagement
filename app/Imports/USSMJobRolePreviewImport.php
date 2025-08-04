<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class USSMJobRolePreviewImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $mappedRow = [
                'nik'           => $row['nik'] ?? null,
                'job_role_id'   => $row['job_role_id'] ?? null,
                'user_type'     => $row['user_type'] ?? null,
                'is_active'     => $row['is_active'] ?? 1,
            ];
            $this->rows->push(collect($mappedRow));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
