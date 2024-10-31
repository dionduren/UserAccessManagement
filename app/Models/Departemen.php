<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departemen extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'kompartemen_id', 'name', 'description', 'created_by', 'updated_by'];

    // A department belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // A department belongs to a compartment
    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class);
    }

    // A department has many job roles
    public function jobRoles()
    {
        return $this->hasMany(JobRole::class);
    }
}