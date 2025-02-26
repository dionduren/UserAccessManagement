<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
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
        'email',
        'grade',
        'jabatan',
        'atasan',
        'cost_code',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

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
}
