<?php

namespace App\Models\middle_db\raw;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\middle_db\MasterDataKaryawan;

class GenericKaryawanMapping extends Model
{
    protected $table = 'mdb_usmm_generic_karyawan_mapping';
    protected $primaryKey = 'id';

    protected $fillable = [
        'sap_user_id',
        'user_full_name',
        'company',
        'personnel_number',
        'employee_full_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke MasterDataKaryawan (sap_user_id -> nik).
     * GenericKaryawanMapping.sap_user_id menyimpan nilai nik dari MasterDataKaryawan.
     */
    public function masterDataKaryawan()
    {
        return $this->belongsTo(MasterDataKaryawan::class, 'sap_user_id', 'nik');
    }

    /**
     * Sinkronisasi dari SQL Server (koneksi eksternal) ke tabel lokal (Postgres).
     * - Jalankan query mapping (ua vs k) seperti yang diminta
     * - Truncate tabel lokal, lalu insert batch
     *
     * @param string $order 'sap_user_id' atau 'user_full_name'
     * @return array{inserted:int}
     */
    public static function syncFromExternal(string $order = 'sap_user_id'): array
    {
        $table = (new self)->getTable();

        // Query sumber (SQL Server / FreeTDS)
        $sql = <<<SQL
            SELECT 
                ua.bname         AS sap_user_id,
                ua.name_textc    AS user_full_name,
                k.company,
                k.emp_no         AS personnel_number,
                k.nama           AS employee_full_name
            FROM basis_user_addr ua
            JOIN basis_karyawan k
                ON LOWER(ua.name_textc COLLATE DATABASE_DEFAULT) = LOWER(k.nama COLLATE DATABASE_DEFAULT)
            WHERE ua.bname NOT LIKE '[0-9]%'  -- Exclude SAP IDs starting with 0–9
            AND LEFT(ua.bname COLLATE DATABASE_DEFAULT, 1) = LEFT(k.company COLLATE DATABASE_DEFAULT, 1)
            ORDER BY {$order}
            SQL;

        // Ambil data dari koneksi eksternal (ganti 'sqlsrv_freetds' sesuai konfigurasi kamu)
        // $rows = DB::connection('sqlsrv_freetds')->select($sql);
        // $rows = DB::connection('sqlsrv_ext')->select($sql);
        $connection = env('SYNC_CONNECTION', 'sqlsrv_ext');
        $rows = DB::connection($connection)->select($sql);

        // Kosongkan tabel lokal
        DB::table($table)->truncate();

        // Insert batch
        $now = now();
        $buf = [];
        $inserted = 0;
        $batchSize = 1000;

        foreach ($rows as $r) {
            $buf[] = [
                'sap_user_id'        => $r->sap_user_id,
                'user_full_name'     => $r->user_full_name,
                'company'            => $r->company,
                'personnel_number'   => $r->personnel_number,
                'employee_full_name' => $r->employee_full_name,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            if (count($buf) >= $batchSize) {
                DB::table($table)->insert($buf);
                $inserted += count($buf);
                $buf = [];
            }
        }

        if (!empty($buf)) {
            DB::table($table)->insert($buf);
            $inserted += count($buf);
        }

        return ['inserted' => $inserted];
    }
}
