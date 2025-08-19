<?php

namespace App\Models\middle_db;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitKerja extends Model
{
    use HasFactory;

    protected $table = 'mdb_unit_kerja';
    protected $primaryKey = 'id';

    protected $fillable = [
        'company',
        'direktorat_id',
        'direktorat',
        'kompartemen_id',
        'kompartemen',
        'departemen_id',
        'departemen',
        'cost_center',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public static function syncFromExternal(): array
    {
        $table = (new self)->getTable();

        // Adjust field mappings below to match actual external columns
        $extRows = DB::connection('sqlsrv_freetds')
            ->table('dbo.BASIS_KARYAWAN')
            ->distinct()
            ->select([
                DB::raw('company'),
                DB::raw('dir_id as direktorat_id'),   // ensure dir_id exists externally
                DB::raw('dir_title as direktorat'),
                DB::raw('komp_id as kompartemen_id'),
                DB::raw('komp_title as kompartemen'),    // ensure komp_title exists; remove if not
                DB::raw('dept_id as departemen_id'),
                DB::raw('dept_title as departemen'),     // ensure dept_title exists; remove if not
                DB::raw('cc_code as cost_center'),
            ])
            ->orderBy('company')
            ->orderBy('dir_id')
            ->orderBy('komp_id')
            ->orderBy('dept_id')
            ->get();

        DB::table($table)->truncate();

        $now = now();
        $buffer = [];
        $inserted = 0;
        $batchSize = 1000;

        foreach ($extRows as $r) {
            $buffer[] = [
                'company'     => $r->company,
                'direktorat_id'  => $r->direktorat_id ?? null,
                'direktorat'     => $r->direktorat ?? null,
                'kompartemen_id' => $r->kompartemen_id ?? null,
                'kompartemen'    => $r->kompartemen ?? null,
                'departemen_id'  => $r->departemen_id ?? null,
                'departemen'     => $r->departemen ?? null,
                'cost_center'    => $r->cost_center ?? null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            if (count($buffer) === $batchSize) {
                DB::table($table)->insert($buffer);
                $inserted += count($buffer);
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
