<?php

namespace App\Models;

use App\Models\Company;
use App\Models\UserDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class userNIK extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_user_ussm_nik';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_code',
        'user_type',
        'license_type',
        'valid_from',
        'valid_to',
        'group',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'nik', 'user_code');
    }

    public function Company()
    {
        return $this->hasOne(Company::class, 'shortname', 'group');
    }
}
