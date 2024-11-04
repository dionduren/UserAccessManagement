<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departemen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_departemen';
    protected $primaryKey = 'id';

    protected $fillable = ['company_id', 'kompartemen_id', 'name', 'description', 'created_by', 'updated_by'];

    // A department belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Define relationship to Kompartemen
    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'id');
    }

    // A department has many job roles
    public function jobRoles()
    {
        return $this->hasMany(JobRole::class);
    }
}
