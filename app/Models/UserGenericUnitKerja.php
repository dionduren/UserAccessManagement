<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Periode;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\UserGeneric;

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
        return $this->belongsTo(UserGeneric::class, 'unit_kerja', 'user_cc');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'kompartemen_id');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id');
    }
}
