<?php

namespace App\Models;

use App\Models\Company;
use App\Models\CostCenter;
use App\Models\CostCurrentUser;
use App\Models\CostPrevUser;
use App\Models\NIKJobRole;
use App\Models\Periode;
use App\Models\UserGenericUnitKerja;
use App\Models\UserNIKUnitKerja;
use App\Models\middle_db\view\GenericKaryawanMappingFiltered;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class userGeneric extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_user_generic';
    protected $primaryKey = 'id';

    protected $fillable = [
        'periode_id',
        'group',
        'user_code',
        'user_type',
        'user_profile',
        'nik',
        'cost_code',
        'keterangan',
        'uar_listed',
        'license_type',
        'last_login',
        'valid_from',
        'valid_to',
        'flagged',
        'keterangan_flagged',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function costCenter()
    {
        return $this->hasOne(CostCenter::class, 'cost_code', 'cost_code');
    }

    public function Company()
    {
        return $this->hasOne(Company::class, 'shortname', 'group');
    }

    // public function unitKerja()
    // {
    //     return $this->hasOne(UserNIKUnitKerja::class, 'nik', 'nik');
    // }

    // public function UserNIKUnitKerja()
    // {
    //     return $this->unitKerja();
    // }

    // public function kompartemen()
    // {
    //     return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    // }

    // public function departemen()
    // {
    //     return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    // }

    // public function jobRole()
    // {
    //     return $this->belongsTo(JobRole::class, 'job_role_id', 'job_role_id');
    // }

    public function currentUser()
    {
        return $this->hasOne(CostCurrentUser::class, 'user_code', 'user_code');
    }

    public function prevUser()
    {
        return $this->hasMany(CostPrevUser::class, 'user_code', 'user_code');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id', 'id');
    }

    public function userGenericUnitKerja()
    {
        return $this->hasOne(UserGenericUnitKerja::class, 'user_cc', 'user_code');
    }

    public function NIKJobRole()
    {
        return $this->hasMany(NIKJobRole::class, 'nik', 'user_code');
    }

    public function mappingNIK()
    {
        return $this->hasOne(GenericKaryawanMappingFiltered::class, 'sap_user_id', 'user_code');
    }
}
