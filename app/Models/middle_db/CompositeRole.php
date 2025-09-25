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

        // Exclude both %-AO and %-AO-% by default
        $excludePatterns = array_values(array_unique([$exclude, '%-AO-%']));

        // Helper to build a clause with variable NOT LIKEs and capture bindings
        $makeClause = function (string $likePattern, array $excludes) {
            $clause = '(AGR_NAME LIKE ?';
            $bindings = [$likePattern];
            foreach ($excludes as $ex) {
                $clause   .= ' AND AGR_NAME NOT LIKE ?';
                $bindings[] = $ex;
            }
            $clause .= ')';
            return [$clause, $bindings];
        };

        // Base clause (e.g., ZM-% with NOT LIKE excludes)
        [$baseClause, $baseBindings] = $makeClause($like, $excludePatterns);

        // Build dynamic OR clauses for extra patterns
        $extraClauses   = [];
        $extraBindings  = [];
        foreach ($extraPatterns as $p) {
            if ($excludeAoForExtras) {
                [$cl, $bd] = $makeClause($p, $excludePatterns);
            } else {
                // Only LIKE (no excludes)
                $cl = '(AGR_NAME LIKE ?)';

                $bd = [$p];
            }
            $extraClauses[]  = $cl;
            $extraBindings[] = $bd;
        }

        $orSql = implode(' OR ', $extraClauses);

        $sql = <<<SQL
            SELECT DISTINCT
                AGR_NAME AS composite_role,
                TEXT     AS description
            FROM BASIS_AGR_TEXTS
            WHERE SPRAS = 'E'
              AND (
                    {$baseClause}
                    OR {$orSql}
                  )
            ORDER BY AGR_NAME
            SQL;

        // Merge bindings in the same order as clauses
        $bindings = array_merge($baseBindings, ...$extraBindings);

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
