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

    protected $fillable = ['company_code', 'name', 'description', 'created_by', 'updated_by'];

    protected $dates = ['deleted_at'];

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
}
