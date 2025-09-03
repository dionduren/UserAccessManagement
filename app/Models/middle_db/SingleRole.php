<?php

namespace App\Models\middle_db;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SingleRole extends Model
{
    use HasFactory;

    protected $table = 'mdb_single_role';

    protected $fillable = [
        'single_role',
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

    public function scopeSapPattern($query)
    {
        return $query->where(function ($q) {
            $q->where('single_role', 'like', 'ZS-%')
                ->orWhere('single_role', 'like', '%-AO%');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('single_role');
    }

    // Sync from external SAP source (BASIS_AGR_TEXTS)
    public static function syncFromExternal(string $like1 = 'ZS-%', string $like2 = '%-AO%'): array
    {
        $connection = env('SYNC_CONNECTION', 'sqlsrv_ext');
        $table = (new self)->getTable();

        $sql = <<<SQL
                    SELECT 
                        AGR_NAME AS single_role,
                        TEXT     AS description
                    FROM BASIS_AGR_TEXTS
                    WHERE SPRAS = 'E'
                    AND (AGR_NAME LIKE ? OR AGR_NAME LIKE ?)
                    ORDER BY AGR_NAME
                    SQL;

        $rows = DB::connection($connection)->select($sql, [$like1, $like2]);

        DB::table($table)->truncate();

        $now = now();
        $buffer = [];
        $batch = 1000;
        $inserted = 0;

        foreach ($rows as $r) {
            $buffer[] = [
                'single_role' => $r->single_role,
                'definisi'    => $r->description,
                'created_at'  => $now,
                'updated_at'  => $now,
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
