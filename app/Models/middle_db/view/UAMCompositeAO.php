<?php

namespace App\Models\middle_db\views;

use Illuminate\Database\Eloquent\Model;

class UAMCompositeAO extends Model
{
    protected $table = 'v_uam_composite_single_ao';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
