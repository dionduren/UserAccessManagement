<?php

namespace App\Imports;

use App\Models\SingleRole;
use App\Models\CompositeRole;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompositeRoleSingleRoleImport implements ToModel, WithHeadingRow, WithChunkReading, WithMultipleSheets
{
    public bool $sheetFound = false;

    public function sheets(): array
    {
        // Only process the sheet named exactly "UPLOAD_TEMPLATE"
        return [
            'UPLOAD_TEMPLATE' => $this,
        ];
    }

    public function model(array $row)
    {
        // Mark that the expected sheet was processed
        $this->sheetFound = true;

        $companyId          = $row['company_id'] ?? null;
        $compositeRoleName  = $this->cleanValue($row['composite_role'] ?? '');
        $singleRoleName     = $this->cleanValue($row['single_role'] ?? '');
        $compositeDescInput = trim($row['composite_description'] ?? '');
        $singleDescInput    = trim($row['single_description'] ?? '');

        if ($compositeRoleName === '') {
            return null;
        }

        $compositeRole = CompositeRole::firstOrNew(['nama' => $compositeRoleName]);

        if (! $compositeRole->exists) {
            $compositeRole->fill([
                'deskripsi'  => $compositeDescInput !== '' ? $compositeDescInput : null,
                'company_code' => $companyId,
                'source'     => 'upload',
            ]);
            $compositeRole->save();
        } else {
            $dirty = false;
            if ($compositeDescInput !== '' && $compositeRole->deskripsi !== $compositeDescInput) {
                $compositeRole->deskripsi = $compositeDescInput;
                $dirty = true;
            }
            if ($companyId && $compositeRole->company_id !== $companyId) {
                $compositeRole->company_id = $companyId;
                $dirty = true;
            }
            if ($dirty) {
                $compositeRole->save();
            }
        }

        if ($singleRoleName !== '') {
            $singleRole = SingleRole::firstOrNew(['nama' => $singleRoleName]);

            if (! $singleRole->exists) {
                $singleRole->fill([
                    'deskripsi' => $singleDescInput !== '' ? $singleDescInput : null,
                    'source'    => 'upload',
                ]);
                $singleRole->save();
            } else {
                $dirty = false;
                if ($singleDescInput !== '' && $singleRole->deskripsi !== $singleDescInput) {
                    $singleRole->deskripsi = $singleDescInput;
                    $dirty = true;
                }
                if ($dirty) {
                    $singleRole->save();
                }
            }

            $compositeRole->singleRoles()->syncWithoutDetaching([$singleRole->id]);
        }

        return null;
    }

    private function cleanValue(string $value): string
    {
        // Remove all whitespace characters (keep existing behavior)
        return preg_replace('/\s+/', '', $value);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
