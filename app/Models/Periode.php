<?php

namespace App\Models;

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
    public function userDetail()
    {
        return $this->hasMany(UserDetail::class, 'periode_id', 'id');
    }

    public function userNIK()
    {
        return $this->hasMany(UserNIK::class, 'periode_id', 'id');
    }

    public function userGeneric()
    {
        return $this->hasMany(UserGeneric::class, 'periode_id', 'id');
    }
}
