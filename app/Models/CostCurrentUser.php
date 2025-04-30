<?php

namespace App\Models;

use App\Models\userGeneric;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CostCurrentUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_cc_user';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_code',
        'user_name',
        'cost_code',
        'periode_terdaftar',
        'flagged',
        'keterangan',
        'dokumen_keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function genericUser()
    {
        return $this->hasOne(userGeneric::class, 'user_code', 'cost_code');
    }
}
