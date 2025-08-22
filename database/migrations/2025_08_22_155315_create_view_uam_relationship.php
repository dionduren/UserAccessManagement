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
        // RELATIONSHIP VIEWS (pairings)
        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_uam_user_composite AS
            SELECT DISTINCT
                sap_user,
                composite_role
            FROM mdb_uam_relationship_raw
            WHERE composite_role IS NOT NULL
        SQL);

        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_uam_composite_single AS
            SELECT DISTINCT
                composite_role,
                single_role
            FROM mdb_uam_relationship_raw
            WHERE single_role IS NOT NULL
        SQL);

        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_uam_single_tcode AS
            SELECT DISTINCT
                single_role,
                tcode
            FROM mdb_uam_relationship_raw
            WHERE tcode IS NOT NULL
        SQL);

        // MASTER DATA VIEWS (with descriptions)
        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_uam_composite_master AS
            SELECT DISTINCT
                composite_role,
                composite_role_desc
            FROM mdb_uam_relationship_raw
            WHERE composite_role IS NOT NULL
        SQL);

        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_uam_single_master AS
            SELECT DISTINCT
                single_role,
                single_role_desc
            FROM mdb_uam_relationship_raw
            WHERE single_role IS NOT NULL
        SQL);

        DB::unprepared(<<<SQL
            CREATE OR REPLACE VIEW v_uam_tcode_master AS
            SELECT DISTINCT
                tcode,
                tcode_desc
            FROM mdb_uam_relationship_raw
            WHERE tcode IS NOT NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_uam_tcode_master');
        DB::unprepared('DROP VIEW IF EXISTS v_uam_single_master');
        DB::unprepared('DROP VIEW IF EXISTS v_uam_composite_master');
        DB::unprepared('DROP VIEW IF EXISTS v_uam_single_tcode');
        DB::unprepared('DROP VIEW IF EXISTS v_uam_composite_single');
        DB::unprepared('DROP VIEW IF EXISTS v_uam_user_composite');
    }
};
