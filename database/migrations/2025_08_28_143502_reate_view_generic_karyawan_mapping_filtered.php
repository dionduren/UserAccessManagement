<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * View: v_generic_karyawan_mapping_filtered
     *
     * Memindahkan seluruh logika filter dari controller ke level DB:
     *  - duplicate_name: TRUE jika user_full_name ada di ms_duplicate_name_filter
     *  - filtered_in   : TRUE jika:
     *        (duplicate_name = FALSE)  -> semua baris lolos
     *        (duplicate_name = TRUE)   -> hanya baris dengan personnel_number = nik di filter (nama sama)
     *  - WHERE clause memastikan hanya baris yang lolos (filtered_in) yang ada di view.
     */
    public function up(): void
    {
        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_generic_karyawan_mapping_filtered AS
            SELECT
                g.id,
                g.sap_user_id,
                g.user_full_name,
                g.company,
                g.personnel_number,
                g.employee_full_name,
                /* Nama berada di daftar duplikat? */
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM ms_duplicate_name_filter f
                        WHERE f.nama = g.user_full_name
                    ) THEN 1 ELSE 0
                END AS duplicate_name,
                /* Baris lolos filter? */
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM ms_duplicate_name_filter f
                        WHERE f.nama = g.user_full_name
                    )
                    THEN
                        CASE
                            WHEN EXISTS (
                                SELECT 1 FROM ms_duplicate_name_filter f2
                                WHERE f2.nama = g.user_full_name
                                  AND f2.nik  = g.personnel_number
                            ) THEN 1 ELSE 0
                        END
                    ELSE 1
                END AS filtered_in
            FROM mdb_usmm_generic_karyawan_mapping g
            WHERE
                /* Hanya baris yang lolos aturan (sama seperti kondisi di controller sebelumnya) */
                (
                    NOT EXISTS (
                        SELECT 1 FROM ms_duplicate_name_filter f
                        WHERE f.nama = g.user_full_name
                    )
                    OR
                    EXISTS (
                        SELECT 1 FROM ms_duplicate_name_filter f2
                        WHERE f2.nama = g.user_full_name
                          AND f2.nik  = g.personnel_number
                    )
                );
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_generic_karyawan_mapping_filtered');
    }
};
