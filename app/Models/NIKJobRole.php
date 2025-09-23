<?php

namespace App\Models;

use App\Models\JobRole;
use App\Models\Periode;
use App\Models\UserDetail;
use App\Models\middle_db\MasterUSMM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class NIKJobRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_ussm_job_role';
    protected $primaryKey = 'id';

    protected $fillable = [
        'periode_id',
        'nik',
        'user_type',
        'job_role_id',
        // 'definisi',
        'is_active',
        'last_update',
        'flagged',
        'keterangan_flagged',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function unitKerja()
    {
        return $this->hasOne(UserNIKUnitKerja::class, 'nik', 'nik');
    }

    public function UserNIKUnitKerja()
    {
        return $this->unitKerja();
    }

    public function jobRole()
    {
        // Change from job_role_id to job_role_id
        return $this->belongsTo(JobRole::class, 'job_role_id', 'job_role_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id', 'id');
    }

    public function userGeneric()
    {
        return $this->belongsTo(userGeneric::class, 'nik', 'user_code');
    }

    public function userNIK()
    {
        return $this->belongsTo(userNIK::class, 'nik', 'user_code');
    }

    public function mdb_usmm()
    {
        return $this->belongsTo(MasterUSMM::class, 'nik', 'sap_user_id');
    }
}
