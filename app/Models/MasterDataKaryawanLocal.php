<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\CostCenter;
use App\Models\middle_db\MasterUSMM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterDataKaryawanLocal extends Model
{
    use HasFactory;

    protected $table = 'ms_master_data_karyawan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nik',
        'nama',
        'company',
        'direktorat_id',
        'direktorat',
        'kompartemen_id',
        'kompartemen',
        'departemen_id',
        'departemen',
        'atasan',
        'cost_center',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function company_data()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    }

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function userNIK()
    {
        return $this->hasOne(userNIK::class, 'user_code', 'nik');
    }

    public function userGeneric()
    {
        return $this->hasOne(userGeneric::class, 'user_code', 'nik');
    }

    public function costCenter()
    {
        return $this->hasMany(CostCenter::class, 'cost_code', 'cost_code');
    }
}
