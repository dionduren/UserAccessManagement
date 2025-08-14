<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MsPenomoranUARSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $data = [
            ['company_id' => 'A000', 'kompartemen_id' => '50000686', 'departemen_id' => null,        'unit_kerja_id' => '50000686', 'number' => 101],
            ['company_id' => 'A000', 'kompartemen_id' => '50000706', 'departemen_id' => '50000709',  'unit_kerja_id' => '50000709', 'number' => 301],
            ['company_id' => 'A000', 'kompartemen_id' => '50083682', 'departemen_id' => '50078507',  'unit_kerja_id' => '50078507', 'number' => 302],
            ['company_id' => 'A000', 'kompartemen_id' => '50083682', 'departemen_id' => '50078506',  'unit_kerja_id' => '50078506', 'number' => 303],
            ['company_id' => 'A000', 'kompartemen_id' => '50083682', 'departemen_id' => '50078504',  'unit_kerja_id' => '50078504', 'number' => 304],
            ['company_id' => 'A000', 'kompartemen_id' => '50083682', 'departemen_id' => '50044468',  'unit_kerja_id' => '50044468', 'number' => 305],
            ['company_id' => 'A000', 'kompartemen_id' => '50083682', 'departemen_id' => '50078503',  'unit_kerja_id' => '50078503', 'number' => 306],
            ['company_id' => 'A000', 'kompartemen_id' => '50019459', 'departemen_id' => '50019460',  'unit_kerja_id' => '50019460', 'number' => 307],
            ['company_id' => 'A000', 'kompartemen_id' => '50019459', 'departemen_id' => '50019461',  'unit_kerja_id' => '50019461', 'number' => 308],
            ['company_id' => 'A000', 'kompartemen_id' => '50078236', 'departemen_id' => '50039727',  'unit_kerja_id' => '50039727', 'number' => 309],
            ['company_id' => 'A000', 'kompartemen_id' => '50078236', 'departemen_id' => '50015100',  'unit_kerja_id' => '50015100', 'number' => 310],
            ['company_id' => 'A000', 'kompartemen_id' => '50095059', 'departemen_id' => null,        'unit_kerja_id' => '50095059', 'number' => 311],
            ['company_id' => 'A000', 'kompartemen_id' => '50110856', 'departemen_id' => '50110858',  'unit_kerja_id' => '50110858', 'number' => 312],
            ['company_id' => 'A000', 'kompartemen_id' => '50039338', 'departemen_id' => '50039341',  'unit_kerja_id' => '50039341', 'number' => 401],
            ['company_id' => 'A000', 'kompartemen_id' => '50039338', 'departemen_id' => '50039339',  'unit_kerja_id' => '50039339', 'number' => 402],
            ['company_id' => 'A000', 'kompartemen_id' => '50039338', 'departemen_id' => '50039343',  'unit_kerja_id' => '50039343', 'number' => 403],
            ['company_id' => 'A000', 'kompartemen_id' => '50000690', 'departemen_id' => '50039348',  'unit_kerja_id' => '50039348', 'number' => 404],
            ['company_id' => 'A000', 'kompartemen_id' => '50000690', 'departemen_id' => '50039332',  'unit_kerja_id' => '50039332', 'number' => 405],
            ['company_id' => 'A000', 'kompartemen_id' => '50000690', 'departemen_id' => '50039346',  'unit_kerja_id' => '50039346', 'number' => 406],
            ['company_id' => 'A000', 'kompartemen_id' => '50039327', 'departemen_id' => '50039328',  'unit_kerja_id' => '50039328', 'number' => 407],
            ['company_id' => 'A000', 'kompartemen_id' => '50039327', 'departemen_id' => '50064010',  'unit_kerja_id' => '50064010', 'number' => 408],
            ['company_id' => 'A000', 'kompartemen_id' => '50039327', 'departemen_id' => '50039334',  'unit_kerja_id' => '50039334', 'number' => 409],
            ['company_id' => 'A000', 'kompartemen_id' => '50038702', 'departemen_id' => null,        'unit_kerja_id' => '50038702', 'number' => 501],
            ['company_id' => 'A000', 'kompartemen_id' => '50098707', 'departemen_id' => null,        'unit_kerja_id' => '50098707', 'number' => 502],
            ['company_id' => 'A000', 'kompartemen_id' => '50038751', 'departemen_id' => null,        'unit_kerja_id' => '50038751', 'number' => 601],
            ['company_id' => 'A000', 'kompartemen_id' => '50039675', 'departemen_id' => null,        'unit_kerja_id' => '50039675', 'number' => 602],
            ['company_id' => 'A000', 'kompartemen_id' => '50097200', 'departemen_id' => null,        'unit_kerja_id' => '50097200', 'number' => 603],
            ['company_id' => 'A000', 'kompartemen_id' => '50111089', 'departemen_id' => '50111213',  'unit_kerja_id' => '50111213', 'number' => 704],
            ['company_id' => 'A000', 'kompartemen_id' => '50111091', 'departemen_id' => '50111233',  'unit_kerja_id' => '50111233', 'number' => 717],
            ['company_id' => 'A000', 'kompartemen_id' => '50111090', 'departemen_id' => '50111194',  'unit_kerja_id' => '50111194', 'number' => 718],
            ['company_id' => 'A000', 'kompartemen_id' => '50111090', 'departemen_id' => '50111191',  'unit_kerja_id' => '50111191', 'number' => 719],
            ['company_id' => 'A000', 'kompartemen_id' => '50111090', 'departemen_id' => '50111190',  'unit_kerja_id' => '50111190', 'number' => 720],
            ['company_id' => 'A000', 'kompartemen_id' => '50111088', 'departemen_id' => '50111175',  'unit_kerja_id' => '50111175', 'number' => 721],
            ['company_id' => 'A000', 'kompartemen_id' => '50111088', 'departemen_id' => '50110947',  'unit_kerja_id' => '50110947', 'number' => 722],
        ];

        foreach ($data as &$item) {
            $item['created_by'] = 'Seeder';
            $item['updated_by'] = 'Seeder';
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        DB::table('ms_penomoran_uar')->insert($data);
    }
}
