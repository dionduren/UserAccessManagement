<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompositeRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'jabatan_id', 'nama', 'deskripsi', 'created_by', 'updated_by'];

    // A composite role can have many single roles
    public function singleRoles()
    {
        return $this->hasMany(SingleRole::class);
    }

    // A composite role belongs to a job role
    public function jobRole()
    {
        return $this->belongsTo(JobRole::class, 'jabatan_id');
    }

    // A composite role belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
