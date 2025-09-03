<?php

namespace App\Models\middle_db;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tcode extends Model
{
    use HasFactory;

    protected $table = 'mdb_tcode';

    protected $fillable = [
        'tcode',
        'definisi',
    ];

    public function getDescriptionAttribute(): ?string
    {
        return $this->definisi;
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['definisi'] = $value;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('tcode');
    }

    // Sync from external SAP sources
    public static function syncFromExternal(): array
    {
        $connection = env('SYNC_CONNECTION', 'sqlsrv_ext');
        $table = (new self)->getTable();

        $sql = <<<SQL
            SELECT DISTINCT 
                a.LOW   AS tcode,
                t.TTEXT AS description
            FROM BASIS_AGR_1251 a
            INNER JOIN BASIS_TSTCT t
                ON a.LOW = t.TCODE
                AND t.SPRSL = 'E'
            WHERE a.OBJECT = 'S_TCODE'
            AND a.FIELD  = 'TCD'
            AND a.AGR_NAME NOT IN (SELECT AGR_NAME FROM BASIS_AGR_AGRS)
            ORDER BY a.LOW
            SQL;

        $rows = DB::connection($connection)->select($sql);

        DB::table($table)->truncate();

        $now = now();
        $buffer = [];
        $batch = 1000;
        $inserted = 0;

        foreach ($rows as $r) {
            $buffer[] = [
                'tcode'      => $r->tcode,
                'definisi'   => $r->description,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($buffer) >= $batch) {
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
