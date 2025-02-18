<?php

namespace App\Models;

use App\Models\Company;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class userGeneric extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tr_user_ussm_generic';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_code',
        'user_type',
        'cost_code',
        'license_type',
        'group',
        'valid_from',
        'valid_to',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    public function costCenter()
    {
        return $this->hasOne(CostCenter::class, 'cost_code', 'cost_code');
    }

    public function Company()
    {
        return $this->hasOne(Company::class, 'shortname', 'group');
    }
}
