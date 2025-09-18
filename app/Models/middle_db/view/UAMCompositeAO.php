<?php

namespace App\Models\middle_db\view;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\middle_db\CompositeRole;
use App\Models\middle_db\SingleRole;

class UAMCompositeAO extends Model
{
    protected $table = 'v_uam_composite_single_ao';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    // composite_role (view) -> mdb_composite_role.composite_role
    public function compositeRole(): BelongsTo
    {
        return $this->belongsTo(CompositeRole::class, 'composite_role', 'composite_role');
    }

    // single_role (view, AO) -> mdb_single_role.single_role (equals composite_role . '-AO')
    public function singleRole(): BelongsTo
    {
        return $this->belongsTo(SingleRole::class, 'single_role', 'single_role');
    }
}
