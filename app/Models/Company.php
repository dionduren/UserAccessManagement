<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_company';
    protected $primaryKey = 'company_code';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'company_code',
        'nama',
        'shortname',
        'deskripsi',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // A company has many compartments (kompartemen)
    public function kompartemen()
    {
        return $this->hasMany(Kompartemen::class, 'company_id', 'company_code');
    }

    // A company has many departments (departemen)
    public function departemen()
    {
        return $this->hasMany(Departemen::class, 'company_id', 'company_code');
    }

    // A company has many job roles
    public function jobRoles()
    {
        return $this->hasMany(JobRole::class, 'company_id', 'company_code');
    }

    public function jobRolesWithoutRelations()
    {
        return $this->hasMany(JobRole::class, 'company_id', 'company_code')
            ->whereNull('kompartemen_id')
            ->whereNull('departemen_id');
    }

    public function departemenWithoutKompartemen()
    {
        return $this->hasMany(Departemen::class, 'company_id', 'company_code')
            ->whereNull('kompartemen_id');
    }

    public function singleRoles()
    {
        return $this->hasMany(SingleRole::class, 'company_id');
    }
}
