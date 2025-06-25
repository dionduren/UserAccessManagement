<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Periode;
use App\Models\CostCenter;
use App\Models\CostPrevUser;
use App\Models\CostCurrentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class userGeneric extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_user_generic';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_code',
        'user_type',
        'cost_code',
        'periode_id',
        'license_type',
        'group',
        'valid_from',
        'valid_to',
        'pic',
        'unit_kerja',
        'kompartemen_id',
        'departemen_id',
        'keterangan',
        'error_kompartemen_id',
        'error_departemen_id',
        'flagged',
        'keterangan_flagged',
        'last_login',
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

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    }

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
}
