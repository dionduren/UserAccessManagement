<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class USSMJobRolePreviewImport implements WithMultipleSheets
{
    use Importable;

    public Collection $rows;

    // holds allowed sheet keys (e.g. 'UPLOAD_TEMPLATE' or 0)
    private array $limitSheets = [];

    public function __construct()
    {
        $this->rows = collect();
    }

    // Custom helper to select which sheets to read
    public function onlySheets(...$sheets): self
    {
        // allow passing an array or varargs
        $list = count($sheets) === 1 && is_array($sheets[0]) ? $sheets[0] : $sheets;
        // normalize types (keep strict compare later)
        $this->limitSheets = array_map(function ($k) {
            return is_numeric($k) ? (int) $k : (string) $k;
        }, $list);

        return $this;
    }

    public function sheets(): array
    {
        $map = [
            'UPLOAD_TEMPLATE' => new USSMJobRoleSheetImport($this->rows),
            0                 => new USSMJobRoleSheetImport($this->rows), // fallback: first sheet
        ];

        if (!empty($this->limitSheets)) {
            $filtered = [];
            foreach ($map as $key => $handler) {
                if (in_array($key, $this->limitSheets, true)) {
                    $filtered[$key] = $handler;
                }
            }
            return $filtered ?: $map;
        }

        return $map;
    }
}

// Handles a single sheet’s rows
class USSMJobRoleSheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(private Collection $sink) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $mappedRow = [
                'nik'         => $row['nik'] ?? null,
                'job_role_id' => $row['job_role_id'] ?? null,
                'user_type'   => $row['user_type'] ?? null,
            ];
            $this->sink->push(collect($mappedRow));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
