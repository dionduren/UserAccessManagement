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

    // List of columns in the view
    /**
     * @property int $id
     * @property string|null $sap_user_id
     * @property string|null $user_full_name
     * @property string|null $company
     * @property string|null $personnel_number
     * @property string|null $employee_full_name
     * @property bool $duplicate_name
     * @property bool $filtered_in
     */


    protected $casts = [
        'duplicate_name' => 'boolean',
        'filtered_in'    => 'boolean',
    ];
}
