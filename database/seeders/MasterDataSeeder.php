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
        // Company::create([
        //     'company_code' => 'A000',
        //     'name' => 'Pupuk Indonesia',
        //     'description' => 'Pupuk Indonesia Holding Company',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);

        // Company::create([
        //     'company_code' => 'B000',
        //     'name' => 'Anak Perusahaan',
        //     'description' => 'Pupuk Indonesia Holding Company',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);

        // Array of companies data
        $companies = [
            ["kode_perusahaan" => "A000", "nama_perusahaan" => "PT Pupuk Indonesia (Persero)", "singkatan" => "PI"],
            ["kode_perusahaan" => "B000", "nama_perusahaan" => "PT Petrokimia Gresik", "singkatan" => "PKG"],
            ["kode_perusahaan" => "C000", "nama_perusahaan" => "PT Pupuk Kujang Cikampek", "singkatan" => "PKC"],
            ["kode_perusahaan" => "D000", "nama_perusahaan" => "PT Pupuk Kalimantan Timur", "singkatan" => "PKT"],
            ["kode_perusahaan" => "E000", "nama_perusahaan" => "PT Pupuk Iskandar Muda", "singkatan" => "PIM"],
            ["kode_perusahaan" => "F000", "nama_perusahaan" => "PT Pupuk Sriwidjaja Palembang", "singkatan" => "PSP"],
            ["kode_perusahaan" => "G000", "nama_perusahaan" => "PT Rekayasa Industri", "singkatan" => "REKIND"],
            ["kode_perusahaan" => "H000", "nama_perusahaan" => "PT Pupuk Indonesia Niaga", "singkatan" => "PI NIAGA"],
            ["kode_perusahaan" => "I000", "nama_perusahaan" => "PT Pupuk Indonesia Logistik", "singkatan" => "PILOG"],
            ["kode_perusahaan" => "J000", "nama_perusahaan" => "PT Pupuk Indonesia Utilitas", "singkatan" => "PIU"],
            ["kode_perusahaan" => "JA00", "nama_perusahaan" => "PT Kaltim Daya Mandiri", "singkatan" => "KDM"],
            ["kode_perusahaan" => "K000", "nama_perusahaan" => "PT Pupuk Indonesia Pangan", "singkatan" => "PIP"],
            ["kode_perusahaan" => "Z000", "nama_perusahaan" => "Forum Human Capital Indonesia", "singkatan" => "FHCI"]
        ];

        // Insert each company into the database
        foreach ($companies as $company) {
            Company::create([
                'company_code' => $company['kode_perusahaan'],
                'name' => $company['nama_perusahaan'],
                'shortname' => $company['singkatan'],
                'description' => null, // Optional description field, set to null or a default value if needed
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ]);
        }

        $this->command->info('Master data Company seeded successfully.');

        // Seed Kompartemens
        // Kompartemen::create([
        //     'company_id' => '1',
        //     'name' => 'Kompartemen Keuangan dan Akuntansi',
        //     'description' => 'Kompartemen Keuangan PIHC',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Kompartemen::create([
        //     'company_id' => '1',
        //     'name' => 'Kompartemen Teknologi Informasi',
        //     'description' => 'Kompartemen Teknologi Informasi PIHC',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Kompartemen::create([
        //     'company_id' => '2',
        //     'name' => 'Kompartemen Keuangan dan Akuntansi',
        //     'description' => 'Kompartemen Keuangan Anak Perusahaan',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Kompartemen::create([
        //     'company_id' => '2',
        //     'name' => 'Kompartemen Teknik',
        //     'description' => 'Kompartemen Teknik Anak Perusahaan',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);

        // // Seed Departemens
        // Departemen::create([
        //     'company_id' => '1',
        //     'kompartemen_id' => '1',
        //     'name' => 'Departemen Keuangan',
        //     'description' => 'Departemen Keuangan PIHC',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Departemen::create([
        //     'company_id' => '1',
        //     'kompartemen_id' => '1',
        //     'name' => 'Departemen Akuntansi',
        //     'description' => 'Departemen Akuntansi PIHC',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Departemen::create([
        //     'company_id' => '1',
        //     'kompartemen_id' => '2',
        //     'name' => 'Departemen Operasional TI',
        //     'description' => 'Departemen OSTI PIHC',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Departemen::create([
        //     'company_id' => '1',
        //     'kompartemen_id' => '2',
        //     'name' => 'Departemen Infrastruktur & Layanan TI',
        //     'description' => 'Departemen Infra & Layanan PIHC',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // // Seed Departemens for Company ID 2
        // Departemen::create([
        //     'company_id' => '2',
        //     'kompartemen_id' => '3', // Assuming this is "Kompartemen Keuangan dan Akuntansi" for Company ID 2
        //     'name' => 'Departemen Keuangan',
        //     'description' => 'Departemen Keuangan Anak Perusahaan',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Departemen::create([
        //     'company_id' => '2',
        //     'kompartemen_id' => '3', // Assuming this is "Kompartemen Keuangan dan Akuntansi" for Company ID 2
        //     'name' => 'Departemen Akuntansi',
        //     'description' => 'Departemen Akuntansi Anak Perusahaan',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Departemen::create([
        //     'company_id' => '2',
        //     'kompartemen_id' => '4', // Assuming this is "Kompartemen Teknik" for Company ID 2
        //     'name' => 'Departemen Teknik',
        //     'description' => 'Departemen Teknik Anak Perusahaan',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);
        // Departemen::create([
        //     'company_id' => '2',
        //     'kompartemen_id' => '4', // Assuming this is "Kompartemen Teknik" for Company ID 2
        //     'name' => 'Departemen Operasi Teknik',
        //     'description' => 'Departemen Operasi Teknik Anak Perusahaan',
        //     'created_by' => 'seeder',
        //     'updated_by' => 'seeder'
        // ]);

        // // Retrieve all departments and seed two job roles for each
        // $departemens = Departemen::all();

        // foreach ($departemens as $departemen) {
        //     // Retrieve the associated kompartemen and company
        //     $kompartemen = $departemen->kompartemen;
        //     $company = $departemen->company;

        //     // Seed two job roles for each department
        //     JobRole::create([
        //         'company_id' => $company->id,
        //         'kompartemen_id' => $kompartemen->id,
        //         'departemen_id' => $departemen->id,
        //         'nama_jabatan' => 'Manager ' . $departemen->name,
        //         'deskripsi' => 'Responsible for managing the ' . $departemen->name,
        //         'created_by' => 1, // Assuming the user with ID 1 is the seeder user
        //         'updated_by' => 1,
        //     ]);

        //     JobRole::create([
        //         'company_id' => $company->id,
        //         'kompartemen_id' => $kompartemen->id,
        //         'departemen_id' => $departemen->id,
        //         'nama_jabatan' => 'Assistant Manager ' . $departemen->name,
        //         'deskripsi' => 'Assists in managing the ' . $departemen->name,
        //         'created_by' => 1,
        //         'updated_by' => 1,
        //     ]);
        // }

        // $this->command->info('Job roles seeded successfully.');

        $this->command->info('Master data seeded successfully.');
    }
}
