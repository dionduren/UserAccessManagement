<?php

namespace App\Models\middle_db\views;

use Illuminate\Database\Eloquent\Model;

class UAMSingleMaster extends Model
{
    protected $table = 'v_uam_single_master';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
