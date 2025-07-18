<?php

namespace App\Models;

use App\Models\JobRole;
use App\Models\Periode;
use App\Models\UserDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NIKJobRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_ussm_job_role';
    protected $primaryKey = 'id';

    protected $fillable = [
        'periode_id',
        'nik',
        'job_role_id',
        'definisi',
        'is_active',
        'last_update',
        'flagged',
        'keterangan_flagged',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function userDetail()
    {
        // Change from hasOne to belongsTo since UserDetail is the parent table
        return $this->belongsTo(UserDetail::class, 'nik', 'nik');
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
        return $this->belongsTo(UserGeneric::class, 'user_code', 'nik');
    }

    public function userNIK()
    {
        return $this->belongsTo(userNIK::class, 'nik', 'user_code');
    }
}
