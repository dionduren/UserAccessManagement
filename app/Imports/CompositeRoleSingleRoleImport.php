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
        // Clean targeted fields (remove all whitespace characters)
        $compositeRoleName = $this->cleanValue($row['composite_role'] ?? '');
        $singleRoleName = $this->cleanValue($row['single_role'] ?? '');

        // Step 1: Validate or create Composite Role
        if ($compositeRoleName !== '') {
            $compositeRole = CompositeRole::firstOrCreate([
                'nama' => $compositeRoleName,
            ], [
                'deskripsi' => $row['composite_description'] ?? null,
                'company_id' => $row['company_id'] ?? null,
                'source' => 'upload',
            ]);

            // Step 2: Validate or create Single Role
            if ($singleRoleName !== '') {
                $singleRole = SingleRole::firstOrCreate([
                    'nama' => $singleRoleName,
                    'company_id' => $row['company_id'] ?? null,
                ], [
                    'deskripsi' => $row['single_description'] ?? null,
                    'source' => 'upload'
                ]);

                // Step 3: Link Single Role to Composite Role
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
