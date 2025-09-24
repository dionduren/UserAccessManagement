<?php

namespace App\Services;

use App\Models\NIKJobRole;
use App\Models\JobRole;
use Illuminate\Support\Facades\Auth;

class USSMJobRoleService
{
    /**
     * Handle a single row. Returns an array describing the result.
     * ['uploaded' => bool, 'nik' => string|null, 'job_role_id' => string|null, 'reason' => string|null]
     */
    public function handleRow(array $row): array
    {
        $user = Auth::user()?->name ?? 'system';

        // Only allow create/update if job_role_id exists in JobRole (by job_role_id column)
        $jobRoleId = $row['job_role_id'] ?? null;
        $nik = $row['nik'] ?? null;

        if (!$jobRoleId || !JobRole::where('job_role_id', $jobRoleId)->exists()) {
            return [
                'uploaded' => false,
                'nik' => $nik,
                'job_role_id' => $jobRoleId,
                'reason' => 'Job Role not found',
            ];
        }

        // Build flagged message (unchanged)
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
                'periode_id' => $row['periode_id'],
                'nik'        => $nik,
            ],
            [
                'job_role_id'        => $jobRoleId,
                'user_type'          => $row['user_type'] ?? null,
                'is_active'          => $row['is_active'] ?? 1,
                'last_update'        => now(),
                'flagged'            => $flagged,
                'keterangan_flagged' => $msg ?: null,
                'created_by'         => $user,
                'updated_by'         => $user,
            ]
        );

        return [
            'uploaded' => true,
            'nik' => $nik,
            'job_role_id' => $jobRoleId,
            'reason' => null,
        ];
    }
}
