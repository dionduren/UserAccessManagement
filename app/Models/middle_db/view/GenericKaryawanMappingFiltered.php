<?php

namespace App\Models\middle_db\view;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk view v_generic_karyawan_mapping_filtered
 */
class GenericKaryawanMappingFiltered extends Model
{
    protected $table = 'v_generic_karyawan_mapping_filtered';
    public $incrementing = false;
    public $timestamps   = false;
    protected $guarded   = [];

    protected $casts = [
        'duplicate_name' => 'boolean',
        'filtered_in'    => 'boolean',
    ];
}
