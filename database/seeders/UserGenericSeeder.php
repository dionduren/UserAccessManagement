<?php

namespace Database\Seeders;

use App\Models\userGeneric;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserGenericSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to JSON file
        $jsonFile = database_path('seeders/data/user_generic.json');

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
        foreach ($data as $userGeneric) {
            $userGeneric['periode_id'] = 1;

            if (!empty($userGeneric['valid_from'])) {
                $userGeneric['valid_from'] = Carbon::createFromFormat('d.m.Y', $userGeneric['valid_from'])->format('Y-m-d');
            } else {
                $userGeneric['valid_from'] = null;
            }
            if (!empty($userGeneric['valid_to'])) {
                $userGeneric['valid_to'] = Carbon::createFromFormat('d.m.Y', $userGeneric['valid_to'])->format('Y-m-d');
            } else {
                $userGeneric['valid_to'] = null;
            }

            userGeneric::updateOrCreate(
                ['user_code' => $userGeneric['user_code']],
                $userGeneric
            );
        }

        $this->command->info('User Generic data seeded successfully!');
    }
}
