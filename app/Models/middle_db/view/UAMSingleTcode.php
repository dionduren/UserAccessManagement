<?php

namespace App\Models\middle_db\view;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\middle_db\SingleRole;
use App\Models\middle_db\Tcode;

class UAMSingleTcode extends Model
{
    protected $table = 'v_uam_single_tcode';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    // single_role (view) -> mdb_single_role.single_role
    public function singleRole(): BelongsTo
    {
        return $this->belongsTo(SingleRole::class, 'single_role', 'single_role');
    }

    // tcode (view) -> mdb_tcode.tcode
    public function tcode(): BelongsTo
    {
        return $this->belongsTo(Tcode::class, 'tcode', 'tcode');
    }
}
