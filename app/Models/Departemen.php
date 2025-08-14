<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departemen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_departemen';
    protected $primaryKey = 'departemen_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'departemen_id',
        'company_id',
        'kompartemen_id',
        'cost_center',
        'nama',
        'deskripsi',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    // A department belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_code');
    }

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function jobRoles()
    {
        return $this->hasMany(JobRole::class, 'departemen_id', 'departemen_id');
    }

    public function costCenter()
    {
        return $this->hasOne(CostCenter::class, 'cost_center', 'cost_center');
    }
}
