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
    'composite_role_desc',
    'single_role',
    'single_role_desc',
    'tcode',
    'tcode_desc',
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
            SELECT DISTINCT
                au.UNAME        AS sap_user,
                ca.AGR_NAME     AS composite_role,
                cr_desc.TEXT    AS composite_role_desc,
                ca.CHILD_AGR    AS single_role,
                sr_desc.TEXT    AS single_role_desc,
                a.LOW           AS tcode,
                tc.TTEXT        AS tcode_desc
            FROM BASIS_AGR_AGRS AS ca
            JOIN BASIS_AGR_1251 AS a
              ON a.AGR_NAME = ca.CHILD_AGR
             AND a.OBJECT   = 'S_TCODE'
             AND a.FIELD    = 'TCD'
            JOIN BASIS_AGR_USERS AS au
              ON au.AGR_NAME = ca.AGR_NAME
            OUTER APPLY (
              SELECT TOP (1) t.TEXT
              FROM BASIS_AGR_TEXTS t
              WHERE t.AGR_NAME = ca.AGR_NAME
                AND t.SPRAS = 'E'
              ORDER BY t.LINE
            ) AS cr_desc
            OUTER APPLY (
              SELECT TOP (1) t.TEXT
              FROM BASIS_AGR_TEXTS t
              WHERE t.AGR_NAME = ca.CHILD_AGR
                AND t.SPRAS = 'E'
              ORDER BY t.LINE
            ) AS sr_desc
            LEFT JOIN BASIS_TSTCT tc
              ON tc.TCODE = a.LOW
             AND tc.SPRSL = 'E'
            WHERE ca.AGR_NAME LIKE ?
            ORDER BY au.UNAME, ca.AGR_NAME, ca.CHILD_AGR, a.LOW
        SQL;


    $rows = DB::connection('sqlsrv_freetds')->select($sql, [$roleLike]);
    // $rows = DB::connection('sqlsrv_ext')->select($sql, [$roleLike]);

    DB::table($table)->truncate();

    $now = now();
    $buf = [];
    $inserted = 0;
    $batch = 1000;

    foreach ($rows as $r) {
      $buf[] = [
        'sap_user'             => $r->sap_user,
        'composite_role'       => $r->composite_role,
        'composite_role_desc'  => $r->composite_role_desc,
        'single_role'          => $r->single_role,
        'single_role_desc'     => $r->single_role_desc,
        'tcode'                => $r->tcode,
        'tcode_desc'           => $r->tcode_desc,
        'created_at'           => $now,
        'updated_at'           => $now,
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
