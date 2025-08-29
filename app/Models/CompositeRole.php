<?php

namespace App\Models;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\SingleRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\middle_db\raw\UAMRelationshipRAW;

class CompositeRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_composite_roles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'company_id',
        'kompartemen_id',
        'departemen_id',
        'jabatan_id',
        'nama',
        'deskripsi',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];


    // A composite role belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_code');
    }

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class, 'jabatan_id', 'id');
    }

    // A composite role can have many single roles
    public function singleRoles()
    {
        return $this->belongsToMany(SingleRole::class, 'pt_composite_role_single_role', 'composite_role_id', 'single_role_id')
            ->withPivot('created_by', 'updated_by')
            ->withTimestamps();
    }

    /**
     * Breakdown of connected Single Roles and Tcodes.
     * Sources combined:
     *  - Middle DB RAW (UAMRelationshipRAW) filtered by this composite role name.
     *  - Local pivot relations (singleRoles + their tcodes).
     *
     * Returns Collection with 2 rows (metric = single_roles|tcodes) and columns:
     *  raw_count          : distinct items from RAW
     *  local_count        : distinct items from LOCAL
     *  overlap_count      : items present in BOTH
     *  raw_only_count     : items only in RAW
     *  local_only_count   : items only in LOCAL
     *  total_distinct     : union distinct count
     *
     * Optionally (when $withLists = true) also adds:
     *  raw_items, local_items, overlap_items, raw_only_items, local_only_items
     */
    public function aggregatedConnectivityCounts(bool $withLists = false): \Illuminate\Support\Collection
    {
        if (!$this->nama) {
            $emptyRow = [
                'raw_count' => 0,
                'local_count' => 0,
                'overlap_count' => 0,
                'raw_only_count' => 0,
                'local_only_count' => 0,
                'total_distinct' => 0,
            ];
            if ($withLists) {
                $emptyLists = [
                    'raw_items' => [],
                    'local_items' => [],
                    'overlap_items' => [],
                    'raw_only_items' => [],
                    'local_only_items' => [],
                ];
                $emptyRow = array_merge($emptyRow, $emptyLists);
            }
            return collect([
                array_merge(['metric' => 'single_roles'], $emptyRow),
                array_merge(['metric' => 'tcodes'], $emptyRow),
            ]);
        }

        // RAW side
        $rawSingles = UAMRelationshipRAW::where('composite_role', $this->nama)
            ->whereNotNull('single_role')
            ->pluck('single_role')
            ->filter()
            ->unique()
            ->values();

        $rawTcodes = UAMRelationshipRAW::where('composite_role', $this->nama)
            ->whereNotNull('tcode')
            ->pluck('tcode')
            ->filter()
            ->unique()
            ->values();

        // LOCAL side
        $localSingles = $this->singleRoles()
            ->pluck('tr_single_roles.nama') // explicit table for safety
            ->filter()
            ->unique()
            ->values();

        // Local tcodes via pivot (avoid N+1)
        $localTcodes = \DB::table('pt_composite_role_single_role as crsr')
            ->join('pt_single_role_tcode as srt', 'srt.single_role_id', '=', 'crsr.single_role_id')
            ->join('tr_tcodes as tc', 'tc.id', '=', 'srt.tcode_id')
            ->where('crsr.composite_role_id', $this->id)
            ->pluck('tc.code')
            ->filter()
            ->unique()
            ->values();

        // Helper closure to build breakdown
        $build = function ($rawSet, $localSet, string $metric) use ($withLists) {
            $overlap      = $rawSet->intersect($localSet)->values();
            $rawOnly      = $rawSet->diff($localSet)->values();
            $localOnly    = $localSet->diff($rawSet)->values();
            $unionDistinct = $rawSet->merge($localSet)->unique()->values();

            $row = [
                'metric'           => $metric,
                'raw_count'        => $rawSet->count(),
                'local_count'      => $localSet->count(),
                'overlap_count'    => $overlap->count(),
                'raw_only_count'   => $rawOnly->count(),
                'local_only_count' => $localOnly->count(),
                'total_distinct'   => $unionDistinct->count(),
            ];

            if ($withLists) {
                $row = array_merge($row, [
                    'raw_items'        => $rawSet->values()->all(),
                    'local_items'      => $localSet->values()->all(),
                    'overlap_items'    => $overlap->values()->all(),
                    'raw_only_items'   => $rawOnly->values()->all(),
                    'local_only_items' => $localOnly->values()->all(),
                ]);
            }

            return $row;
        };

        return collect([
            $build($rawSingles, $localSingles, 'single_roles'),
            $build($rawTcodes,  $localTcodes,  'tcodes'),
        ]);
    }
}
