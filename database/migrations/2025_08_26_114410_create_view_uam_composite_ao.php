<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        /**
         * View relasi composite_role - single_role
         * Hanya yang salah satu (atau keduanya) berakhiran '-AO'
         * (Sesuai permintaan menggunakan LIKE '%-AO%')
         *
         * Catatan:
         * - Secara ketat "berakhiran -AO" seharusnya pola LIKE '%-AO'
         *   tetapi mengikuti instruksi: LIKE '%-AO%'
         *   Jika ingin benar‑benar di akhir, ganti '%-AO%' menjadi '%-AO'
         */
        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_uam_composite_single_ao AS
            SELECT DISTINCT
                composite_role,
                single_role
            FROM mdb_uam_relationship_raw
            WHERE
                (
                    composite_role IS NOT NULL
                    AND composite_role <> ''
                    AND composite_role LIKE '%-AO%'
                )
                OR
                (
                    single_role IS NOT NULL
                    AND single_role <> ''
                    AND single_role LIKE '%-AO%'
                )
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_uam_composite_single_ao');
    }
};
