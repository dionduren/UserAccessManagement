<?php

namespace App\Models;

use App\Models\UserNIK;
use App\Models\UserGeneric;
use App\Models\UserGenericUnitKerja;
use App\Models\UserNIKUnitKerja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_periode';
    protected $primaryKey = 'id';

    protected $fillable = [
        'definisi',
        'tanggal_create_periode',
        'is_active',
    ];

    protected $dates = ['deleted_at'];


    // Eloquent Relationships
    public function unitKerjaNIK()
    {
        return $this->hasMany(UserNIKUnitKerja::class, 'periode_id', 'id');
    }

    public function unitKerjaGeneric()
    {
        return $this->hasMany(UserGenericUnitKerja::class, 'periode_id', 'id');
    }

    public function userNIK()
    {
        return $this->hasMany(UserNIK::class, 'periode_id', 'id');
    }

    public function userGeneric()
    {
        return $this->hasMany(userGeneric::class, 'periode_id', 'id');
    }
}
