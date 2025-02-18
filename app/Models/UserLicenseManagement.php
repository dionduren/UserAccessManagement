<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Departemen;
use App\Models\Kompartemen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLicenseManagement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_sap_license_type';
    protected $primaryKey = 'id';

    protected $fillable = [
        'license_type',
        'contract_license_type',
        'deleted_by',
    ];

    protected $dates = ['deleted_at'];

    // CostCenter has a User
    public function User()
    {
        return $this->belongsTo(User::class, 'license_type', 'license_type');
    }
}
