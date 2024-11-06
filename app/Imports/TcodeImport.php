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
        // Get headers from the first row
        $headers = $rows->first()->toArray();

        // Iterate through each subsequent row
        foreach ($rows->skip(1) as $row) {
            // Convert row to array and combine with headers
            $row = collect(array_combine($headers, $row->toArray()));

            // Extract values using header keys
            $companyCode = $row->get('Company');
            $code = $row->get('Tcode');
            $description = $row->get('Tcode Desc');
            $singleRoleName = $row->get('Single Role');
            $singleRoleDesc = $row->get('Single Role Desc');

            $this->data[] = [
                'company_id' => $companyCode, // Assuming 'company_id' corresponds to this
                'code' => $code,
                'deskripsi' => $description,
                'single_role_name' => $singleRoleName, // Pass Single Role name as is
                'single_role_desc' => $singleRoleDesc, // Pass Single Role description as is
            ];
        }
    }


    public function getData()
    {
        return $this->data;
    }
}
