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
        // Step 1: Validate or create Composite Role
        $compositeRole = CompositeRole::firstOrCreate([
            'nama' => $row['composite_role'],
        ], [
            'deskripsi' => $row['composite_description'] ?? null,
            'company_id' => $row['company_id'] ?? null,
        ]);

        // Step 2: Validate or create Single Role
        if (!empty($row['single_role'])) {
            $singleRole = SingleRole::firstOrCreate([
                'nama' => $row['single_role'],
                'company_id' => $row['company_id'] ?? null,
            ], [
                'deskripsi' => $row['single_description'] ?? null,
            ]);

            // Step 3: Link Single Role to Composite Role
            $compositeRole->singleRoles()->syncWithoutDetaching([$singleRole->id]);
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
