<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\userNIK;
use App\Models\userGeneric;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CostCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_cost_center';
    protected $primaryKey = 'id';

    protected $fillable = [
        'group',
        'cost_center',
        'cost_code',
        'deskripsi',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    // CostCenter belongs to a Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'shortname', 'group');
    }


    public function userNIK()
    {
        return $this->hasMany(userNIK::class, 'cost_code', 'cost_code');
    }

    public function userGeneric()
    {
        return $this->hasMany(userGeneric::class, 'cost_code', 'cost_code');
    }
}
