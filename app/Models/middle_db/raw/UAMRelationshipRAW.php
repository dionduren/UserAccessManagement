<?php

namespace App\Models\middle_db\raw;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UAMRelationshipRAW extends Model
{
    protected $table = 'mdb_uam_relationship_raw';
    protected $primaryKey = 'id';

    protected $fillable = [
        'sap_user',
        'composite_role',
        'single_role',
        'tcode',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Sinkronisasi dari SQL Server (FreeTDS/dblib) ke tabel RAW lokal.
     * - Truncate tabel lokal
     * - Insert batch
     */
    public static function syncFromExternal(string $roleLike = 'Z%'): array
    {
        $table = (new self)->getTable();

        $sql = <<<SQL
                SELECT
                    au.uname    AS sap_user,
                    ca.agr_name AS composite_role,
                    a.agr_name  AS single_role,
                    a.low       AS tcode
                FROM basis_agr_agrs ca
                JOIN basis_agr_1251 a  ON ca.child_agr = a.agr_name
                JOIN basis_agr_users au ON ca.agr_name = au.agr_name
                WHERE a.object = 'S_TCODE'
                AND a.field  = 'TCD'
                AND ca.agr_name LIKE ?
                ORDER BY ca.agr_name, a.agr_name, a.low
                SQL;

        // Ganti 'sqlsrv_freetds' sesuai nama koneksi eksternalmu (dblib).
        $rows = DB::connection('sqlsrv_freetds')->select($sql, [$roleLike]);
        // $rows = DB::connection('sqlsrv_ext')->select($sql, [$roleLike]);

        // Kosongkan tabel RAW
        DB::table($table)->truncate();

        // Insert batch
        $now = now();
        $buf = [];
        $inserted = 0;
        $batch = 1000;

        foreach ($rows as $r) {
            $buf[] = [
                'sap_user'       => $r->sap_user,
                'composite_role' => $r->composite_role,
                'single_role'    => $r->single_role,
                'tcode'          => $r->tcode,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            if (count($buf) >= $batch) {
                DB::table($table)->insert($buf);
                $inserted += count($buf);
                $buf = [];
            }
        }
        if ($buf) {
            DB::table($table)->insert($buf);
            $inserted += count($buf);
        }

        return ['inserted' => $inserted];
    }
}
