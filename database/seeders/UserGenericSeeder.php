<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\userGeneric;

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
            userGeneric::updateOrCreate(
                ['user_code' => $userGeneric['user_code']],
                $userGeneric
            );
        }
    }
}
