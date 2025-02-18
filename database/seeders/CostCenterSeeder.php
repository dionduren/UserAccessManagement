<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CostCenter;
use Illuminate\Support\Facades\File;

class CostCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Path to JSON file
        $jsonFile = database_path('seeders/data/cost_center.json');

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
        foreach ($data as $costCenter) {
            CostCenter::updateOrCreate(
                ['cost_center' => $costCenter['cost_center']],
                $costCenter
            );
        }

        $this->command->info('Cost center data seeded successfully!');
    }
}
