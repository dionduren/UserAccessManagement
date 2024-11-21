<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_company';
    protected $primaryKey = 'id';

    protected $fillable = ['company_code', 'name', 'shortname', 'description', 'created_by', 'updated_by'];

    protected $dates = ['deleted_at'];

    // ensures default values are returned if a related model is missing.
    public function company()
    {
        return $this->belongsTo(Company::class)->withDefault([
            'name' => 'N/A',
        ]);
    }


    // A company has many compartments (kompartemen)
    public function kompartemen()
    {
        return $this->hasMany(Kompartemen::class);
    }

    // A company has many departments (departemen)
    public function departemen()
    {
        return $this->hasMany(Departemen::class);
    }

    // A company has many job roles
    public function jobRoles()
    {
        return $this->hasMany(JobRole::class);
    }

    public function jobRolesWithoutRelations()
    {
        return $this->hasMany(JobRole::class)->whereNull('kompartemen_id')->whereNull('departemen_id');
    }

    public function departemenWithoutKompartemen()
    {
        return $this->hasMany(Departemen::class)->whereNull('kompartemen_id');
    }
}
