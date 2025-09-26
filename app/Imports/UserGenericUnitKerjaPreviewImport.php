<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UserGenericUnitKerjaPreviewImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    use Importable;

    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function sheets(): array
    {
        return [
            'UPLOAD_TEMPLATE' => new class($this) implements ToCollection, WithHeadingRow {
                public function __construct(private UserGenericUnitKerjaPreviewImport $parent) {}

                public function collection(Collection $rows): void
                {
                    $this->parent->rows = $rows;
                }
            },
        ];
    }

    public function onUnknownSheet($sheetName): void
    {
        // ignore other sheets
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $mappedRow = [
                'user_cc' => $row['user_code'] ?? null,
                'periode_id' => $row['periode_id'] ?? null,
                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                'departemen_id' => $row['departemen_id'] ?? null,
                'error_kompartemen_id' => $row['error_kompartemen_id'] ?? null,
                'error_departemen_id' => $row['error_departemen_id'] ?? null,
                'flagged' => $row['flagged'] ?? null,
                'keterangan_flagged' => $row['keterangan_flagged'] ?? null,
            ];
            $this->rows->push(collect($mappedRow));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
