<?php

namespace Database\Seeders;

use App\Models\userNIK;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserNIKSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to JSON file
        $jsonFile = database_path('seeders/data/user_nik.json');

        // Check if the JSON file exists
        if (!File::exists($jsonFile)) {
            $this->command->error("Cost centers JSON file not found at: {$jsonFile}");
            return;
        }

        // Read and decode JSON data
        $data = json_decode(File::get($jsonFile), true);

        if (empty($data)) {
            $this->command->error('No data found in the cost centers JSON file.');
            return;
        }

        // Insert data into the database
        foreach ($data as $user) {
            // Insert into UserNIK
            UserNIK::updateOrCreate(
                ['user_code' => $user['user_code']],
                [
                    'user_type' => $user['user_type'],
                    'license_type' => $user['license_type'],
                    'valid_from' => $user['valid_from'] ?? null,
                    'valid_to' => $user['valid_to'] ?? null,
                    'group' => $user['group'],
                    'created_by' => "Seeder",
                    'updated_by' => "Seeder"
                ]
            );

            // Insert into UserDetail
            UserDetail::updateOrCreate(
                ['nik' => $user['user_code']],
                [
                    'nama' => $user['nama'],
                    'email' => $user['Email'],
                    'company_id' => $user['company_id'],
                    'direktorat' => $user['direktorat'],
                    'kompartemen_id' => $user['kompartemen_id'],
                    'departemen_id' => $user['departemen_id'],
                    'grade' => $user['grade'],
                    'jabatan' => $user['jabatan'],
                    'atasan' => $user['atasan'],
                    'cost_center' => $user['cost_center'],
                    'created_by' => "Seeder",
                    'updated_by' => "Seeder"
                ]
            );
        }

        $this->command->info('User NIK & User Detail data seeded successfully!');
    }
}
