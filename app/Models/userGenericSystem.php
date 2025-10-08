<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Periode;
use App\Models\CostCenter;
use App\Models\Company;

class userGenericSystem extends Model
{
    use HasFactory;

    protected $table = 'ms_non_sap_uid';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_code',
        'user_type',
        'user_profile',
        'nik',
        'cost_code',
        'license_type',
        'group',
        'periode_id',
        'last_login',
        'keterangan',
        'uar_listed',
        'error_kompartemen_id',
        'error_departemen_id',
        'flagged',
        'keterangan_flagged',
        'valid_from',
        'valid_to',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'valid_from' => 'date',
        'valid_to'   => 'date',
        'flagged'    => 'boolean',
        'uar_listed' => 'boolean',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id', 'id');
    }

    public function costCenter()
    {
        return $this->hasOne(CostCenter::class, 'cost_code', 'cost_code');
    }

    public function company()
    {
        // group holds company shortname (mirroring userGeneric)
        return $this->hasOne(Company::class, 'shortname', 'group');
    }


    public function userGenericUnitKerja()
    {
        return $this->hasOne(UserGenericUnitKerja::class, 'user_cc', 'user_code');
    }

    public function NIKJobRole()
    {
        return $this->hasMany(NIKJobRole::class, 'nik', 'user_code');
    }
}
