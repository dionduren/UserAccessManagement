<?php

namespace App\Services;

use App\Models\NIKJobRole;
use App\Models\Periode;
use App\Models\JobRole;
use Illuminate\Support\Facades\Auth;

class USSMJobRoleService
{
    public function handleRow(array $row): void
    {
        $user = Auth::user()?->name ?? 'system';

        // Compose the validation message as in your controller
        $msg = '';
        if (!empty($row['_row_warnings'])) {
            $msg .= "Warnings:\n- " . implode("\n- ", $row['_row_warnings']) . "\n";
        }
        if (!empty($row['_row_errors'])) {
            $msg .= "Errors:\n- " . implode("\n- ", $row['_row_errors']);
        }
        $msg = trim($msg);

        $flagged = !empty($row['_row_errors']) || !empty($row['_row_warnings']);

        NIKJobRole::updateOrCreate(
            [
                'periode_id'   => $row['periode_id'],
                'nik'          => $row['nik'],
                'job_role_id'  => $row['job_role_id'],
            ],
            [
                'definisi'           => $row['definisi'] ?? null,
                'is_active'          => $row['is_active'] ?? 1,
                'last_update'        => now(),
                'flagged'            => $flagged,
                'keterangan_flagged' => $msg ?: null,
                'created_by'         => $user,
                'updated_by'         => $user,
            ]
        );
    }
}
