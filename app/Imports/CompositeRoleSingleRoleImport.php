<?php

namespace App\Imports;

use App\Models\SingleRole;
use App\Models\CompositeRole;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CompositeRoleSingleRoleImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        $companyId          = $row['company_id'] ?? null;
        $compositeRoleName  = $this->cleanValue($row['composite_role'] ?? '');
        $singleRoleName     = $this->cleanValue($row['single_role'] ?? '');
        $compositeDescInput = trim($row['composite_description'] ?? '');
        $singleDescInput    = trim($row['single_description'] ?? '');

        if ($compositeRoleName !== '') {
            $compositeRole = CompositeRole::firstOrNew(['nama' => $compositeRoleName]);

            if (! $compositeRole->exists) {
                $compositeRole->fill([
                    'deskripsi' => $compositeDescInput ?: null,
                    'company_id' => $companyId,
                    'source' => 'upload',
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
                $singleRole = SingleRole::firstOrNew([
                    'nama' => $singleRoleName
                ]);

                if (! $singleRole->exists) {
                    $singleRole->fill([
                        'deskripsi' => $singleDescInput ?: null,
                        'source' => 'upload',
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
        }
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
