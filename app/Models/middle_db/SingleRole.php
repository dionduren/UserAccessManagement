<?php

namespace App\Models\middle_db;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\middle_db\view\UAMCompositeSingle;
use App\Models\middle_db\view\UAMSingleTcode;
use App\Models\middle_db\view\UAMCompositeAO; // add

class SingleRole extends Model
{
    use HasFactory;

    protected $table = 'mdb_single_role';
    protected $primaryKey = 'single_role';      // <-- add
    public $incrementing = false;               // <-- add
    protected $keyType = 'string';              // <-- add
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

    // View rows linking this single role to composite roles (read-only)
    public function compositeSingles(): HasMany
    {
        return $this->hasMany(UAMCompositeSingle::class, 'single_role', 'single_role');
    }

    // Composite roles via mapping view
    public function compositeRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            CompositeRole::class,
            'v_uam_composite_single',
            'single_role',     // foreign key on pivot referencing this model
            'composite_role',  // foreign key on pivot referencing related model
            'single_role',     // local key on this model
            'composite_role'   // local key on related model
        );
    }

    // View rows linking this single role to tcodes (read-only)
    public function singleTcodes(): HasMany
    {
        return $this->hasMany(UAMSingleTcode::class, 'single_role', 'single_role');
    }

    // Tcodes via mapping view
    public function tcodes(): BelongsToMany
    {
        return $this->belongsToMany(
            Tcode::class,
            'v_uam_single_tcode',   // pivot/view
            'single_role',          // FK on pivot to this model
            'tcode',                // FK on pivot to related model
            'single_role',          // local key on this model
            'tcode'                 // local key on related model
        );
    }

    // View rows linking this AO single role to composite roles (read-only)
    public function compositeAOs(): HasMany
    {
        return $this->hasMany(UAMCompositeAO::class, 'single_role', 'single_role');
    }

    // Composite roles via AO mapping view
    public function aoCompositeRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            CompositeRole::class,
            'v_uam_composite_single_ao',
            'single_role',     // FK on view referencing this model
            'composite_role',  // FK on view referencing related model
            'single_role',     // local key on this model
            'composite_role'   // local key on related model
        );
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
