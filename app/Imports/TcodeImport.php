<?php

namespace App\Imports;

use App\Models\SingleRole;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TcodeImport implements ToCollection
{
    private $data = [];

    public function collection(Collection $rows)
    {
        // Skip the header row and start from row 2
        foreach ($rows->skip(1) as $row) {
            $singleRoleNames = explode(',', $row[3]);
            $singleRoleIds = [];
            $missingRoles = []; // Track missing roles for the current row

            foreach ($singleRoleNames as $name) {
                $singleRole = SingleRole::where('nama', trim($name))->first();
                if ($singleRole) {
                    $singleRoleIds[] = $singleRole->id;
                } else {
                    // Add to missingRoles for this specific Tcode if the role is missing
                    $missingRoles[] = trim($name);
                }
            }

            $this->data[] = [
                'company_id' => $row[0],
                'code' => $row[1],
                'deskripsi' => $row[2],
                'single_roles' => $singleRoleIds, // Store as array of IDs
                'missing_roles' => $missingRoles, // Store specific missing roles per Tcode
            ];
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
