<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kompartemen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_kompartemen';
    protected $primaryKey = 'id';

    protected $fillable = ['company_id', 'name', 'description', 'created_by', 'updated_by'];

    protected $dates = ['deleted_at'];

    // A compartment belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // A compartment has many departments
    public function departemen()
    {
        return $this->hasMany(Departemen::class);
    }

    public function jobRoles()
    {
        return $this->hasMany(JobRole::class);
    }
}
