<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\File;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\UserDetail;
use App\Models\Kompartemen;
use App\Models\CostPrevUser;
use App\Models\CostCurrentUser;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GenericUserListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to JSON file
        $jsonFile = database_path('seeders/data/cost_user_list.json');

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

        foreach ($data as $user) {
            // Create CostPrevUser (only create, do not update)
            CostPrevUser::create([
                'user_code'          => $user['prev_nik'],
                'user_name'          => $user['prev_user'],
                'cost_code'          => $user['cost_code'],
                'dokumen_keterangan' => null,
                'created_at'         => now(),
                'updated_at'         => now(),
                'created_by'         => 'Seeder',
                'updated_by'         => 'Seeder',
            ]);

            // Update or create CostCurrentUser using cost_code as the unique key
            CostCurrentUser::updateOrCreate(
                ['cost_code' => $user['cost_code']],
                [
                    'user_name'          => $user['current_user'],
                    'user_code'          => $user['current_nik'],
                    'dokumen_keterangan' => $user['filename'],
                    'cost_code'          => $user['cost_code'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                    'created_by'         => 'Seeder',
                    'updated_by'         => 'Seeder',
                ]
            );

            // Look up company_id from Company model where shortname equals the JSON 'group'
            $companyId = Company::where('shortname', $user['group'])->value('id');

            // Look up kompartemen_id from Kompartemen model where name equals the JSON 'Kompartemen'
            $kompartemenId = Kompartemen::where('name', $user['Kompartemen'])->value('id');

            // Look up departemen_id from Departemen model where name equals the JSON 'Departemen'
            $departemenId = Departemen::where('name', $user['Departemen'])->value('id');

            // Create UserDetail if not existing using current_nik as the unique key
            UserDetail::firstOrCreate(
                ['nik' => $user['current_nik']],
                [
                    'nama'           => $user['current_user'],
                    'nik'            => $user['current_nik'],
                    'company_id'     => $companyId,
                    'kompartemen_id' => $kompartemenId,
                    'departemen_id'  => $departemenId,
                    'jabatan'        => $user['Jabatan'],
                    'cost_center'      => $user['cost_code'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                    'created_by'     => 'Seeder',
                    'updated_by'     => 'Seeder',
                ]
            );
        }

        $this->command->info('Cost Prev, Current, and User Detail data seeded successfully!');
    }
}
