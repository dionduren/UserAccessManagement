<?php

namespace App\Models\middle_db;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompositeRole extends Model
{

    protected $table = 'mdb_composite_role';

    protected $fillable = [
        'composite_role',
        'definisi',
    ];

    // Provide unified attribute name similar to query alias (description)
    public function getDescriptionAttribute(): ?string
    {
        return $this->definisi;
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['definisi'] = $value;
    }

    /**
     * Scope replicating:
     * AGR_NAME LIKE 'ZM-%' AND AGR_NAME NOT LIKE '%-AO'
     */
    public function scopeSapPattern($query)
    {
        return $query
            ->where('composite_role', 'like', 'ZM-%')
            ->where('composite_role', 'not like', '%-AO');
    }

    /**
     * Order like the SQL (ORDER BY AGR_NAME)
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('composite_role');
    }

    // Sync from external SAP source (BASIS_AGR_TEXTS)
    public static function syncFromExternal(string $like = 'ZM-%', string $exclude = '%-AO'): array
    {
        $connection = env('SYNC_CONNECTION', 'sqlsrv_ext');
        $table = (new self)->getTable();

        $sql = <<<SQL
                    SELECT 
                        AGR_NAME AS composite_role,
                        TEXT     AS description
                    FROM BASIS_AGR_TEXTS
                    WHERE SPRAS = 'E'
                    AND AGR_NAME LIKE ?
                    AND AGR_NAME NOT LIKE ?
                    ORDER BY AGR_NAME
                    SQL;

        $rows = DB::connection($connection)->select($sql, [$like, $exclude]);

        DB::table($table)->truncate();

        $now = now();
        $buffer = [];
        $batch = 1000;
        $inserted = 0;

        foreach ($rows as $r) {
            $buffer[] = [
                'composite_role' => $r->composite_role,
                'definisi'       => $r->description,
                'created_at'     => $now,
                'updated_at'     => $now,
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
