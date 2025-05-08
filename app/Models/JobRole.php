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

    protected $fillable = [
        'company_id',
        'kompartemen_id',
        'departemen_id',
        'job_role_id',
        'nama',
        'status',
        'deskripsi',
        'created_by',
        'updated_by',
        'deleted_by',
        'error_kompartemen_name',
        'error_departemen_name',
        'flagged',
        'keterangan',
    ];

    protected $dates = ['deleted_at'];

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

    public function compositeRole()
    {
        return $this->hasOne(CompositeRole::class, 'jabatan_id', 'id');
    }

    public function NIKJobRole()
    {
        return $this->hasMany(NIKJobRole::class, 'job_role_id');
    }
}
