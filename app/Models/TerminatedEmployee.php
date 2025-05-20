<?php

namespace App\Models;

use App\Models\UserDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TerminatedEmployee extends Model
{
    use HasFactory;

    protected $table = 'ms_terminated_employee';

    protected $fillable = [
        'periode_id',
        'nik',
        'nama',
        'tanggal_resign',
        'status',
        'last_login',
        'valid_from',
        'valid_to',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'tanggal_resign',
        'last_login',
        'valid_from',
        'valid_to',
    ];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'nik', 'nik');
    }
}
