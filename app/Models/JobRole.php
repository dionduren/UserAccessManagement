<?php

namespace App\Models;

use App\Models\Company;
use App\Models\CompositeRole;
use App\Models\Departemen;
use App\Models\Kompartemen;

use App\Models\middle_db\raw\UAMRelationshipRAW;
use App\Models\NIKJobRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpOffice\PhpWord\Writer\HTML\Style\Generic;

class JobRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_job_roles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'company_id',
        'kompartemen_id',
        'departemen_id',
        'job_role_id',
        'nama',
        'status',
        'deskripsi',
        'created_by',
        'updated_by',
        'deleted_by',
        'error_kompartemen_id',
        'error_kompartemen_name',
        'error_departemen_id',
        'error_departemen_name',
        'flagged',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

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

    public function compositeRole()
    {
        return $this->hasOne(CompositeRole::class, 'jabatan_id', 'id');
    }

    public function NIKJobRole()
    {
        // Change from job_role_id to id since that's the primary key
        return $this->hasMany(NIKJobRole::class, 'job_role_id', 'job_role_id');
    }

    public function userGenerics()
    {
        return $this->hasMany(userGeneric::class, 'job_role_id', 'job_role_id');
    }

    /**
     * Fetch related composite roles from middle DB (mdb_uam_relationship_raw).
     * A composite role is considered connected when:
     *  - Any NIK of this job role appears as sap_user in RAW table (source = nik)
     *  - OR this job role has a compositeRole->nama that appears as composite_role (source = composite_role)
     *
     * Improvements:
     *  - Proper grouping of OR conditions (previous version risked broad OR when both filters present)
     *  - Can return only names, or detailed debug info (sources, row counts, contributing NIK users)
     *  - Eliminates duplicate composite roles (aggregate in PHP after one query)
     *
     * @param bool $onlyCompositeRoleNames Return only unique composite_role names (Collection of strings)
     * @param bool $withDebug              Return detailed per composite info (Collection of objects)
     * @return \Illuminate\Support\Collection
     */
    public function connectedMiddleCompositeRoles(bool $onlyCompositeRoleNames = false, bool $withDebug = false)
    {
        $niks = $this->NIKJobRole()
            ->pluck('nik')
            ->filter()
            ->unique()
            ->values();

        $compositeName = optional($this->compositeRole)->nama;

        if ($niks->isEmpty() && !$compositeName) {
            return collect();
        }

        // Build base query with proper parentheses around OR parts
        $base = UAMRelationshipRAW::query()
            ->when($niks->isNotEmpty() || $compositeName, function ($q) use ($niks, $compositeName) {
                $q->where(function ($inner) use ($niks, $compositeName) {
                    if ($niks->isNotEmpty()) {
                        $inner->whereIn('sap_user', $niks);
                    }
                    if ($compositeName) {
                        // Use OR inside the parentheses so overall outer logic stays confined
                        $inner->orWhere('composite_role', $compositeName);
                    }
                });
            });

        // We only need minimal columns for aggregation
        $rows = $base->select(
            'id',
            'sap_user',
            'composite_role',
            'composite_role_desc'
        )->whereNotNull('composite_role')
            ->where('composite_role', '<>', '')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        // Group by composite_role
        $grouped = $rows->groupBy('composite_role')->map(function ($group, $comp) use ($niks, $compositeName) {

            $viaNikUsers = $group
                ->pluck('sap_user')
                ->filter(fn($u) => $u && $niks->contains($u))
                ->unique()
                ->values();

            $hasViaNik       = $viaNikUsers->isNotEmpty();
            $hasDirectLinked = $compositeName && $comp === $compositeName;

            $sources = [];
            if ($hasViaNik)       $sources[] = 'nik';
            if ($hasDirectLinked) $sources[] = 'composite_role';

            $first = $group->first()->replicate(); // clone to avoid mutating original model instance
            $first->setAttribute('source', implode('+', $sources));
            $first->setAttribute('source_flags', [
                'via_nik'         => $hasViaNik,
                'via_composite'   => $hasDirectLinked,
            ]);
            $first->setAttribute('nik_users', $viaNikUsers);
            $first->setAttribute('raw_row_count', $group->count());

            return $first;
        })->values();

        if ($onlyCompositeRoleNames) {
            return $grouped->pluck('composite_role')->sort()->values();
        }

        if ($withDebug) {
            // Return enriched collection sorted by composite_role
            return $grouped->sortBy('composite_role')->values();
        }

        // Default (backward compatible): one representative per composite with source info
        return $grouped->sortBy('composite_role')->values();
    }
}
