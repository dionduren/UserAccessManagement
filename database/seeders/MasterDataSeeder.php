<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobRole;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // Seed Companies
        Company::create([
            'company_code' => 'A000',
            'name' => 'Pupuk Indonesia',
            'description' => 'Pupuk Indonesia Holding Company',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'

        ]);
        Company::create([
            'company_code' => 'B000',
            'name' => 'Anak Perusahaan',
            'description' => 'Pupuk Indonesia Holding Company',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);

        // Seed Kompartemens
        Kompartemen::create([
            'company_id' => '1',
            'name' => 'Kompartemen Keuangan dan Akuntansi',
            'description' => 'Kompartemen Keuangan PIHC',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Kompartemen::create([
            'company_id' => '1',
            'name' => 'Kompartemen Teknologi Informasi',
            'description' => 'Kompartemen Teknologi Informasi PIHC',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Kompartemen::create([
            'company_id' => '2',
            'name' => 'Kompartemen Keuangan dan Akuntansi',
            'description' => 'Kompartemen Keuangan Anak Perusahaan',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Kompartemen::create([
            'company_id' => '2',
            'name' => 'Kompartemen Teknik',
            'description' => 'Kompartemen Teknik Anak Perusahaan',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);

        // Seed Departemens
        Departemen::create([
            'company_id' => '1',
            'kompartemen_id' => '1',
            'name' => 'Departemen Keuangan',
            'description' => 'Departemen Keuangan PIHC',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Departemen::create([
            'company_id' => '1',
            'kompartemen_id' => '1',
            'name' => 'Departemen Akuntansi',
            'description' => 'Departemen Akuntansi PIHC',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Departemen::create([
            'company_id' => '1',
            'kompartemen_id' => '2',
            'name' => 'Departemen Operasional TI',
            'description' => 'Departemen OSTI PIHC',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Departemen::create([
            'company_id' => '1',
            'kompartemen_id' => '2',
            'name' => 'Departemen Infrastruktur & Layanan TI',
            'description' => 'Departemen Infra & Layanan PIHC',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        // Seed Departemens for Company ID 2
        Departemen::create([
            'company_id' => '2',
            'kompartemen_id' => '3', // Assuming this is "Kompartemen Keuangan dan Akuntansi" for Company ID 2
            'name' => 'Departemen Keuangan',
            'description' => 'Departemen Keuangan Anak Perusahaan',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Departemen::create([
            'company_id' => '2',
            'kompartemen_id' => '3', // Assuming this is "Kompartemen Keuangan dan Akuntansi" for Company ID 2
            'name' => 'Departemen Akuntansi',
            'description' => 'Departemen Akuntansi Anak Perusahaan',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Departemen::create([
            'company_id' => '2',
            'kompartemen_id' => '4', // Assuming this is "Kompartemen Teknik" for Company ID 2
            'name' => 'Departemen Teknik',
            'description' => 'Departemen Teknik Anak Perusahaan',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);
        Departemen::create([
            'company_id' => '2',
            'kompartemen_id' => '4', // Assuming this is "Kompartemen Teknik" for Company ID 2
            'name' => 'Departemen Operasi Teknik',
            'description' => 'Departemen Operasi Teknik Anak Perusahaan',
            'created_by' => 'seeder',
            'updated_by' => 'seeder'
        ]);

        // Retrieve all departments and seed two job roles for each
        $departemens = Departemen::all();

        foreach ($departemens as $departemen) {
            // Retrieve the associated kompartemen and company
            $kompartemen = $departemen->kompartemen;
            $company = $departemen->company;

            // Seed two job roles for each department
            JobRole::create([
                'company_id' => $company->id,
                'kompartemen_id' => $kompartemen->id,
                'departemen_id' => $departemen->id,
                'nama_jabatan' => 'Manager ' . $departemen->name,
                'deskripsi' => 'Responsible for managing the ' . $departemen->name,
                'created_by' => 1, // Assuming the user with ID 1 is the seeder user
                'updated_by' => 1,
            ]);

            JobRole::create([
                'company_id' => $company->id,
                'kompartemen_id' => $kompartemen->id,
                'departemen_id' => $departemen->id,
                'nama_jabatan' => 'Assistant Manager ' . $departemen->name,
                'deskripsi' => 'Assists in managing the ' . $departemen->name,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }

        $this->command->info('Job roles seeded successfully.');

        $this->command->info('Master data seeded successfully.');
    }
}
