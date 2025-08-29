<?php

namespace App\Models\middle_db;

use App\Models\NIKJobRole;
use App\Models\middle_db\MasterDataKaryawan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterUSMM extends Model
{
    protected $table = 'mdb_usmm_master';
    protected $primaryKey = 'id';

    protected $fillable = [
        'company',
        'sap_user_id',
        'full_name',
        'department',
        'last_logon_date',
        'last_logon_time',
        'user_type',
        'user_type_desc',
        'valid_from',
        'valid_to',
        'contractual_user_type',
        'contr_user_type_desc',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function NIKJobRole()
    {
        return $this->belongsTo(NIKJobRole::class, 'sap_user_id', 'nik');
    }

    public function masterDataKaryawan_nik()
    {
        return $this->belongsTo(MasterDataKaryawan::class, 'sap_user_id', 'nik');
    }

    public function masterDataKaryawan_nama()
    {
        return $this->belongsTo(MasterDataKaryawan::class, 'full_name', 'nama');
    }

    /**
     * Sinkron dari SQL Server (FreeTDS/dblib) ke Postgres lokal:
     * - Truncate tabel
     * - Insert batch
     */
    public static function syncFromExternal(): array
    {
        $table = (new self)->getTable();

        $sql = <<<SQL
                WITH latest_login AS (
                    SELECT 
                        u.bname,
                        u.class,
                        u.trdat,
                        u.ltime,
                        u.ustyp,
                        u.gltgv,
                        u.gltgb,
                        ROW_NUMBER() OVER (
                            PARTITION BY u.bname 
                            ORDER BY u.trdat DESC, u.ltime DESC
                        ) AS rn
                    FROM basis_usr02 u
                    WHERE NULLIF(LTRIM(RTRIM(u.class)), '') IS NOT NULL
                )
                SELECT
                    ll.class                                 AS company,
                    ua.bname                                 AS sap_user_id,
                    ua.name_textc                            AS full_name,
                    ua.department,
                    ll.trdat                                 AS last_logon_date,
                    ll.ltime                                 AS last_logon_time,
                    ll.ustyp                                 AS user_type,
                    CASE ll.ustyp
                        WHEN 'A' THEN 'Dialog User'
                        WHEN 'B' THEN 'System User'
                        ELSE 'Unknown'
                    END                                       AS user_type_desc,
                    ll.gltgv                                 AS valid_from,
                    ll.gltgb                                 AS valid_to,
                    us06.lic_type                            AS contractual_user_type,
                    CASE us06.lic_type
                        WHEN 'CB' THEN 'SAP Professional User'
                        WHEN 'CA' THEN 'SAP Application Developer'
                        ELSE 'Not Assigned'
                    END                                       AS contr_user_type_desc
                FROM basis_user_addr ua
                JOIN latest_login ll
                    ON ll.bname = ua.bname AND ll.rn = 1
                LEFT JOIN basis_usr06 us06
                    ON ua.bname = us06.bname
                WHERE us06.lic_type IN ('CA', 'CB')
                AND ll.ustyp IN ('A','B')
                AND NULLIF(LTRIM(RTRIM(ll.class)), '') IS NOT NULL
                ORDER BY 
                    CASE UPPER(LTRIM(RTRIM(ll.class)))
                        WHEN 'PIHC' THEN 1
                        WHEN 'PKG'  THEN 2
                        WHEN 'PKC'  THEN 3
                        WHEN 'PKT'  THEN 4
                        WHEN 'PIM'  THEN 5
                        WHEN 'PSP'  THEN 6
                        WHEN 'REKIND'  THEN 7
                        WHEN 'PI NIAGA'  THEN 8
                        WHEN 'PILOG'  THEN 9
                        WHEN 'PIU'  THEN 10
                        WHEN 'KDM'  THEN 11
                        WHEN 'PIP'  THEN 12
                        WHEN 'FHCI'  THEN 13
                        WHEN 'PIE'  THEN 14
                        WHEN 'ME'  THEN 15
                        ELSE 999
                    END,
                    us06.lic_type ASC,
                    ua.bname ASC
                SQL;

        // koneksi eksternal: ganti 'sqlsrv_freetds' jika berbeda (mis: 'dblib')
        // $rows = DB::connection('sqlsrv_freetds')->select($sql);
        // $rows = DB::connection('sqlsrv_ext')->select($sql);
        $connection = env('SYNC_CONNECTION', 'sqlsrv_ext');
        $rows = DB::connection($connection)->select($sql);

        DB::table($table)->truncate();

        $now = now();
        $batch = [];
        $inserted = 0;
        $batchSize = 1000;

        foreach ($rows as $r) {
            $batch[] = [
                'company'               => $r->company ?? null,
                'sap_user_id'           => $r->sap_user_id ?? null,
                'full_name'             => $r->full_name ?? null,
                'department'            => $r->department ?? null,
                'last_logon_date'       => $r->last_logon_date ?? null, // YYYYMMDD
                'last_logon_time'       => $r->last_logon_time ?? null, // HHMMSS
                'user_type'             => $r->user_type ?? null,
                'user_type_desc'        => $r->user_type_desc ?? null,
                'valid_from'            => $r->valid_from ?? null,      // YYYYMMDD
                'valid_to'              => $r->valid_to ?? null,        // YYYYMMDD
                'contractual_user_type' => $r->contractual_user_type ?? null,
                'contr_user_type_desc'  => $r->contr_user_type_desc ?? null,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table($table)->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table($table)->insert($batch);
            $inserted += count($batch);
        }

        return ['inserted' => $inserted];
    }
}
