<?php

namespace App\Models\middle_db\views;

use Illuminate\Database\Eloquent\Model;

class UAMUserComposite extends Model
{
    protected $table = 'v_uam_user_composite';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
