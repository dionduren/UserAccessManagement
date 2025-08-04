<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Call RoleSeeder and UserSeeder
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            MasterDataSeeder::class,
            MsSapLicenseTypeSeeder::class,
            MsPenomoranUARSeeder::class,
            MsPenomoranUAMSeeder::class,
            // CostCenterSeeder::class,
            // UserNIKSeeder::class,
            // UserGenericSeeder::class,
        ]);
    }
}
