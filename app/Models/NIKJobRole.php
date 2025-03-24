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

    protected $table = 'tr_nik_job_role';
    protected $primaryKey = 'id';

    protected $fillable = [
        'periode_id',
        'nik',
        'job_role_id',
        'is_active',
        'last_update',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'nik', 'nik');
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class, 'job_role_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id', 'id');
    }
}
