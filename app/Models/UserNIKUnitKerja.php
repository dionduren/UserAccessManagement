<?php

namespace App\Models;

use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Models\NIKJobRole;
use App\Models\Periode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserNIKUnitKerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_nik_unit_kerja';

    protected $fillable = [
        'periode_id',
        'nama',
        'nik',
        'company_id',
        'direktorat_id',
        'kompartemen_id',
        'departemen_id',
        'atasan',
        'cost_center',
        'error_kompartemen_id',
        'error_kompartemen_name',
        'error_departemen_id',
        'error_departemen_name',
        'flagged',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'flagged' => 'boolean',
        'periode_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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

    public function nikJobRoles()
    {
        // Adjust foreign/local keys if your NIKJobRole table uses different columns
        return $this->hasMany(NIKJobRole::class, 'nik', 'nik');
    }
}
