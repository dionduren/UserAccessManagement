<?php

namespace App\Imports;

use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompanyKompartemenPreviewImport implements ToCollection, WithHeadingRow, WithChunkReading, WithMultipleSheets
{
    public Collection $rows;
    public bool $sheetFound = false;
    private array $requiredHeadings = [
        'company',
        'kompartemen_id',
        'kompartemen',
        'departemen_id',
        'departemen',
        'job_function',
        'composite_role'
    ];

    public function __construct()
    {
        $this->rows = collect();
    }

    // Restrict processing to the sheet named exactly "UPLOAD_TEMPLATE"
    public function sheets(): array
    {
        return [
            'UPLOAD_TEMPLATE' => $this, // reuse same instance
        ];
    }

    public function collection(Collection $rows)
    {
        $this->sheetFound = true;

        foreach ($rows as $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $row = collect([
                'company'        => $row['company'] ?? '',
                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                'kompartemen'    => $row['kompartemen'] ?? null,
                'departemen_id'  => $row['departemen_id'] ?? null,
                'departemen'     => $row['departemen'] ?? null,
                'job_function'   => $row['job_function'] ?? '',
                'composite_role' => $this->cleanCompositeRole($row['composite_role'] ?? ''),
            ]);

            $row['status'] = $this->validateRow(
                $row['kompartemen_id'],
                $row['kompartemen'],
                $row['departemen_id'],
                $row['departemen']
            );

            $this->rows->push($row);
        }
    }

    private function isEmptyRow($row): bool
    {
        return collect(['company', 'job_function'])
            ->every(fn($k) => trim((string)($row[$k] ?? '')) === '');
    }

    private function cleanCompositeRole($value): string
    {
        return preg_replace('/\s+/', '', (string)$value);
    }

    private function validateRow($kompartemenId, $kompartemenName, $departemenId, $departemenName)
    {
        if (!empty($kompartemenName) && empty($kompartemenId)) {
            return ['type' => 'warning', 'message' => 'Kompartemen name exists but ID is missing'];
        }
        if (!empty($kompartemenId) && !Kompartemen::find($kompartemenId)) {
            return ['type' => 'error', 'message' => 'Invalid Kompartemen ID'];
        }
        if (!empty($departemenName) && empty($departemenId)) {
            return ['type' => 'warning', 'message' => 'Departemen name exists but ID is missing'];
        }
        if (!empty($departemenId) && !Departemen::find($departemenId)) {
            return ['type' => 'error', 'message' => 'Invalid Departemen ID'];
        }
        return ['type' => 'valid', 'message' => ''];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    // Optional: call after import to ensure headings present (controller can use)
    public function validateHeadings(array $actual): bool
    {
        return empty(array_diff($this->requiredHeadings, $actual));
    }
}
