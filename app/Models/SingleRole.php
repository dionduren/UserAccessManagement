<?php

namespace App\Models;

use App\Models\Tcode;
use App\Models\Company;
use App\Models\CompositeRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SingleRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_single_roles';
    protected $primaryKey = 'id';

    protected $fillable = [
        // 'company_id',
        'nama',
        'deskripsi',
        'source',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at'];

    // public function company()
    // {
    //     return $this->belongsTo(Company::class, 'company_id', 'company_code');
    // }

    // A single role belongs to a composite role
    public function compositeRoles()
    {
        return $this->belongsToMany(CompositeRole::class, 'pt_composite_role_single_role', 'single_role_id', 'composite_role_id')
            ->withPivot('created_by', 'updated_by')
            ->withTimestamps();
    }


    // A single role can have many tcodes (many-to-many relationship)
    public function tcodes()
    {
        return $this->belongsToMany(Tcode::class, 'pt_single_role_tcode', 'single_role_id', 'tcode_id');
    }
}
