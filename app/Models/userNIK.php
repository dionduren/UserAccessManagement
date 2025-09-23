<?php

namespace App\Models;

use \App\Models\UserNIKUnitKerja;
use App\Models\Company;
use App\Models\NIKJobRole;
use App\Models\Periode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class userNIK extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_user_ussm_nik';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_code',
        'user_type',
        'license_type',
        'periode_id',
        'valid_from',
        'valid_to',
        'group',
        'flagged',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function unitKerja()
    {
        return $this->hasOne(UserNIKUnitKerja::class, 'nik', 'user_code');
        // ->where('periode_id', $this->periode_id);
    }

    public function UserNIKUnitKerja()
    {
        return $this->unitKerja();
    }

    public function Company()
    {
        return $this->hasOne(Company::class, 'shortname', 'group');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id', 'id');
    }

    public function jobRole()
    {
        return $this->hasMany(NIKJobRole::class, 'nik', 'user_code');
    }
    public function NIKJobRole()
    {
        return $this->hasMany(NIKJobRole::class, 'nik', 'user_code');
    }
}
