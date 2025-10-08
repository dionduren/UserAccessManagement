<?php

namespace App\Services;

use App\Models\userGenericSystem;
use Illuminate\Support\Facades\Auth;

class UserSystemService
{
    public function handleRow(array $row): void
    {
        $user = Auth::user()?->name ?? 'system';

        $errors   = $row['_row_errors']    ?? [];
        $warnings = $row['_row_warnings']  ?? [];

        $keterangan_flagged = '';
        if ($errors) {
            $keterangan_flagged .= "Errors:\n";
            foreach ($errors as $i => $e) {
                $keterangan_flagged .= ($i + 1) . ". $e\n";
            }
        }
        if ($warnings) {
            $keterangan_flagged .= "Warnings:\n";
            foreach ($warnings as $i => $w) {
                $keterangan_flagged .= ($i + 1) . ". $w\n";
            }
        }
        $keterangan_flagged = trim($keterangan_flagged);
        $flagged = !empty($errors) || !empty($warnings);

        userGenericSystem::updateOrCreate(
            [
                'periode_id'        => $row['periode_id'] ?? null,
                'user_code' => $row['user_code'] ?? null
            ],
            [
                'group'             => $row['group'] ?? null,
                'user_type'         => $row['user_type'] ?? null,
                'user_profile'      => $row['user_profile'] ?? null,
                'nik'               => $row['nik'] ?? null,
                'cost_code'         => $row['cost_code'] ?? null,
                'license_type'      => $row['license_type'] ?? null,
                'keterangan'        => $row['keterangan'] ?? null,
                'uar_listed'        => $row['uar_listed'] ?? null,
                'valid_from'        => $row['valid_from'] ?? null,
                'valid_to'          => $row['valid_to'] ?? null,
                'last_login'        => $row['last_login'] ?? null,
                'flagged'           => $flagged,
                'keterangan_flagged' => $keterangan_flagged ?: null,
                'updated_by'        => $user,
                'created_by'        => $user,
            ]
        );
    }
}
