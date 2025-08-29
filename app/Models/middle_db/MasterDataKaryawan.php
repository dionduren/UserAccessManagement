<?php

namespace App\Models\middle_db;

use App\Models\Company;
use App\Models\CostCenter;
use App\Models\userGEneric;
use App\Models\userNIK;
use App\Models\middle_db\raw\GenericKaryawanMapping;
use App\Models\middle_db\raw\DuplicateNameFilter;
use App\Models\middle_db\MasterUSMM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterDataKaryawan extends Model
{
    use HasFactory;

    protected $table = 'mdb_master_data_karyawan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nik',
        'nama',
        'company',
        'direktorat_id',
        'direktorat',
        'kompartemen_id',
        'kompartemen',
        'departemen_id',
        'departemen',
        'atasan',
        'cost_center',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function company_data()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company');
    }

    // // A job role belongs to a department
    // public function departemen()
    // {
    //     return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    // }

    // // A job role belongs to a compartment
    // public function kompartemen()
    // {
    //     return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    // }

    public function userNIK()
    {
        return $this->hasOne(userNIK::class, 'user_code', 'nik');
    }

    public function userGeneric()
    {
        return $this->hasOne(userGeneric::class, 'user_code', 'nik');
    }

    public function costCenter()
    {
        return $this->hasMany(CostCenter::class, 'cost_code', 'cost_code');
    }

    public function genericKaryawanMapping()
    {
        return $this->hasOne(GenericKaryawanMapping::class, 'sap_user_id', 'nik');
    }

    public function MasterUSMM()
    {
        return $this->hasOne(MasterUSMM::class, 'sap_user_id', 'nik');
    }

    public function duplicateNameFilter()
    {
        return $this->hasMany(DuplicateNameFilter::class, 'nik', 'nik');
    }

    /*
     * Sync data from external SQL Server (sqlsrv_ext) into local table.
     * Truncates local table first.
     * @return array { inserted => int }
     */

    public static function syncFromExternal(): array
    {
        $table = (new self)->getTable();

        // Fetch required columns from external source
        // $rows = DB::connection('sqlsrv_freetds')->select($sql);
        // $rows = DB::connection('sqlsrv_ext')->select($sql);
        $connection = env('SYNC_CONNECTION', 'sqlsrv_ext');
        $extRows = DB::connection($connection)
            ->table('dbo.BASIS_KARYAWAN')
            ->selectRaw("
                emp_no    AS nik,
                nama,
                company,
                dir_id    AS direktorat_id,
                dir_title AS direktorat,
                komp_id   AS kompartemen_id,
                komp_title AS kompartemen,
                dept_id   AS departemen_id,
                dept_title AS departemen,
                sup_emp_no AS atasan,
                cc_code   AS cost_center
            ")
            ->orderBy('company')
            ->orderBy('dir_id')
            ->orderBy('komp_id')
            ->orderBy('dept_id')
            ->get();

        DB::table($table)->truncate();

        $now = now();
        $buffer = [];
        $inserted = 0;

        foreach ($extRows as $r) {
            $buffer[] = [
                'nik'            => $r->nik,
                'nama'           => $r->nama,
                'company'        => $r->company,
                'direktorat_id'  => $r->direktorat_id,
                'direktorat'     => $r->direktorat,
                'kompartemen_id' => $r->kompartemen_id,
                'kompartemen'    => $r->kompartemen,
                'departemen_id'  => $r->departemen_id,
                'departemen'     => $r->departemen,
                'atasan'         => $r->atasan,
                'cost_center'    => $r->cost_center,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            if (count($buffer) === 1000) {
                DB::table($table)->insert($buffer);
                $inserted += 1000;
                $buffer = [];
            }
        }

        if ($buffer) {
            DB::table($table)->insert($buffer);
            $inserted += count($buffer);
        }

        return ['inserted' => $inserted];
    }
}
