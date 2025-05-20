<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Periode;
use App\Models\userNIK;
use App\Models\CostCenter;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_user_detail';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'nik',
        'company_id',
        'license_type',
        'direktorat',
        'kompartemen_id',
        'departemen_id',
        'periode_id',
        'email',
        'atasan',
        'cost_center',
        'cost_code',
        'periode_id',
        'error_kompartemen_id',
        'error_kompartemen_name',
        'error_departemen_id',
        'error_departemen_name',
        'flagged',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function company_data()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_code');
    }

    // A job role belongs to a department
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    }

    // A job role belongs to a compartment
    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function userNIK()
    {
        return $this->hasOne(userNIK::class, 'user_code', 'nik');
    }


    public function user()
    {
        return $this->hasOne(User::class, 'username', 'nik');
    }

    public function costCenter()
    {
        return $this->hasMany(CostCenter::class, 'cost_code', 'cost_code');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'id', 'periode_id');
    }
}
