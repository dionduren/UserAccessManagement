<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MsSapLicenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ms_sap_license_type')->insert([
            [
                'license_type' => 'CA',
                'contract_license_type' => 'SAP Application Developer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'license_type' => 'CB',
                'contract_license_type' => 'SAP Application Professional',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
