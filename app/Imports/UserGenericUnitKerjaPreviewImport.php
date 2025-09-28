<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class UserGenericUnitKerjaPreviewImport implements WithMultipleSheets, SkipsUnknownSheets
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
        $sheets = [
            'UPLOAD_TEMPLATE' => new UserGenericUnitKerjaSheetImport($this->rows),
            0                 => new UserGenericUnitKerjaSheetImport($this->rows), // fallback: first sheet
        ];

        if ($this->limitSheets) {
            return collect($sheets)
                ->only($this->limitSheets)
                ->all();
        }

        return $sheets;
    }

    public function onUnknownSheet($sheetName)
    {
        // ignore other sheets
    }
}

// Handles a single sheet’s rows
class UserGenericUnitKerjaSheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(private Collection $sink) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!array_filter($row->toArray())) {
                continue;
            }

            $mapped = [
                'user_cc'            => $row['user_cc'] ?? null,
                'nama'               => $row['nama'] ?? null,
                'company_code'       => $row['company_code'] ?? null,
                'kompartemen_id'     => $row['kompartemen_id'] ?? null,
                'kompartemen_nama'   => $row['kompartemen_nama'] ?? null,
                'departemen_id'      => $row['departemen_id'] ?? null,
                'departemen_nama'    => $row['departemen_nama'] ?? null,
                'atasan'             => $row['atasan'] ?? null,
                'cost_center'        => $row['cost_center'] ?? null,
                'flagged'            => $row['flagged'] ?? null,
                'keterangan'         => $row['keterangan'] ?? null,
            ];

            $this->sink->push(collect($mapped));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
