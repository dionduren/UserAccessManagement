<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\SingleRole;
use App\Models\CompositeRole;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CompositeRoleSingleRoleImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public $parsedData = [];
    public $errors = [];

    public function model(array $row)
    {
        // Validate Single Role
        $singleRole = SingleRole::where('nama', $row['single_role'])->first();
        if (!$singleRole) {
            Log::warning('Single Role not found', ['single_role' => $row['single_role']]);
            return null;
        }

        // Validate Composite Role
        $compositeRole = CompositeRole::where('nama', $row['composite_role'])->first();
        if (!$compositeRole) {
            Log::warning('Composite Role not found', ['composite_role' => $row['composite_role']]);
            return null;
        }

        // Attach Single Role to Composite Role
        $compositeRole->singleRoles()->syncWithoutDetaching([$singleRole->id]);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $row = $row->toArray();

            // Validate Single Role
            $singleRole = SingleRole::where('nama', $row['single_role'])->first();
            if (!$singleRole) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => ['Single Role not found: ' . $row['single_role']],
                ];
                continue;
            }

            // Validate Composite Role
            $compositeRole = CompositeRole::where('nama', $row['composite_role'])->first();
            if (!$compositeRole) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'errors' => ['Composite Role not found: ' . $row['composite_role']],
                ];
                continue;
            }

            $this->parsedData[] = [
                'single_role' => $singleRole->nama,
                'composite_role' => $compositeRole->nama,
                'description' => $singleRole->deskripsi ?? 'N/A',
            ];
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust the chunk size as needed
    }
}
