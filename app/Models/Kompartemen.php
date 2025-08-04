<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kompartemen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_kompartemen';
    protected $primaryKey = 'kompartemen_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kompartemen_id',
        'company_id',
        'nama',
        'cost_center',
        'deskripsi',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_code');
    }

    public function departemen()
    {
        return $this->hasMany(Departemen::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function jobRoles()
    {
        return $this->hasMany(JobRole::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function costCenter()
    {
        return $this->hasOne(CostCenter::class, 'cost_center', 'cost_center');
    }
}
