<?php

namespace App\Models;

use App\Models\Departemen;
use App\Models\Kompartemen;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenomoranUAM extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_penomoran_uam';

    protected $fillable = [
        'company_id',
        'kompartemen_id',
        'departemen_id',
        'unit_kerja_id',
        'number',
    ];

    protected $dates = ['deleted_at'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_code');
    }

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class, 'unit_kerja_id', 'kompartemen_id');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'unit_kerja_id', 'departemen_id');
    }
}
