<?php

namespace App\Models\middle_db\raw;

use App\Models\Company;
use App\Models\middle_db\MasterDataKaryawan;

use Illuminate\Database\Eloquent\Model;

class DuplicateNameFilter extends Model
{
    protected $table = 'ms_duplicate_name_filter';

    protected $fillable = [
        'company_id',
        'nik',
        'nama',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function source()
    {
        return $this->belongsTo(MasterDataKaryawan::class, 'nik', 'nik');
    }
}
