<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\CompositeRole;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_job_roles';
    protected $primaryKey = 'id';

    protected $fillable = ['company_id', 'kompartemen_id', 'departemen_id', 'nama_jabatan', 'deskripsi', 'created_by', 'updated_by'];

    // A job role belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // A job role belongs to a department
    public function departemen()
    {
        return $this->belongsTo(Departemen::class);
    }

    // A job role belongs to a compartment
    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class);
    }

    // Define the relationship with CompositeRole
    public function compositeRole()
    {
        return $this->hasOne(CompositeRole::class, 'jabatan_id'); // Adjust foreign key if needed
    }
}
