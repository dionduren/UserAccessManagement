<?php

namespace App\Models;

use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\NIKJobRole;
use App\Models\Periode;
use App\Models\userGeneric;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGenericUnitKerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_generic_unit_kerja';

    protected $fillable = [
        'periode_id',
        'user_cc',
        'kompartemen_id',
        'departemen_id',
        'error_kompartemen_id',
        'error_departemen_id',
        'flagged',
        'keterangan_flagged',
    ];

    protected $casts = [
        'flagged' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function userGeneric()
    {
        return $this->belongsTo(userGeneric::class, 'user_cc', 'user_code');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id', 'kompartemen_id');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    }

    public function NIKJobRole()
    {
        return $this->hasMany(NIKJobRole::class, 'user_cc', 'nik');
    }
}
