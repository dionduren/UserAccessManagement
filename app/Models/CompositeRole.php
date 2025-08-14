<?php

namespace App\Models;

use App\Models\Company;
use App\Models\JobRole;
use App\Models\SingleRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
