<?php

namespace App\Models\middle_db;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\middle_db\view\UAMCompositeSingle;
use App\Models\middle_db\view\UAMCompositeAO; // add

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

    // View rows linking this composite to single roles (read-only)
    public function compositeSingles(): HasMany
    {
        return $this->hasMany(UAMCompositeSingle::class, 'composite_role', 'composite_role');
    }

    // Single roles via mapping view
    public function singleRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            SingleRole::class,
            'v_uam_composite_single',
            'composite_role', // foreign key on pivot referencing this model
            'single_role',    // foreign key on pivot referencing related model
            'composite_role', // local key on this model
            'single_role'     // local key on related model
        );
    }

    // View rows linking this composite to its AO single role (read-only)
    public function compositeAOs(): HasMany
    {
        return $this->hasMany(UAMCompositeAO::class, 'composite_role', 'composite_role');
    }

    // AO Single roles via AO mapping view (single_role = composite_role . '-AO')
    public function aoSingleRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            SingleRole::class,
            'v_uam_composite_single_ao',
            'composite_role', // FK on view referencing this model
            'single_role',    // FK on view referencing related model
            'composite_role', // local key on this model
            'single_role'     // local key on related model
        );
    }

    // Sync from external SAP source (BASIS_AGR_TEXTS)
    // Added inclusion of extra patterns: ZPU% and ZS-STKI%
    public static function syncFromExternal(
        string $like = 'ZM-%',
        string $exclude = '%-AO',
        array $extraPatterns = ['ZPU%', 'ZS-STKI%'],
        bool $excludeAoForExtras = true
    ): array {
        $connection = env('SYNC_CONNECTION', 'sqlsrv_ext');
        $table = (new self)->getTable();

        // Build dynamic OR clauses for extra patterns
        $orClauses = [];
        $bindings  = [$like, $exclude];

        foreach ($extraPatterns as $p) {
            if ($excludeAoForExtras) {
                // (AGR_NAME LIKE ? AND AGR_NAME NOT LIKE ?)
                $orClauses[] = '(AGR_NAME LIKE ? AND AGR_NAME NOT LIKE ?)';

                $bindings[]  = $p;
                $bindings[]  = $exclude; // reuse same exclusion pattern
            } else {
                $orClauses[] = '(AGR_NAME LIKE ?)';
                $bindings[]  = $p;
            }
        }

        $orSql = implode(' OR ', $orClauses);

        $sql = <<<SQL
SELECT DISTINCT
    AGR_NAME AS composite_role,
    TEXT     AS description
FROM BASIS_AGR_TEXTS
WHERE SPRAS = 'E'
  AND (
        (AGR_NAME LIKE ? AND AGR_NAME NOT LIKE ?)
        OR $orSql
      )
ORDER BY AGR_NAME
SQL;

        $rows = DB::connection($connection)->select($sql, $bindings);

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
            if (count($buffer) === $batch) {
                DB::table($table)->insert($buffer);
                $inserted += $batch;
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
