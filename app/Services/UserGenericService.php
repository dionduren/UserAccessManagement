<?php

namespace App\Services;

use \App\Models\Departemen;
use \App\Models\JobRole;
use \App\Models\Kompartemen;
use \App\Models\Periode;
use App\Models\userGeneric;
use Illuminate\Support\Facades\Auth;

class UserGenericService
{
    public function handleRow(array $row): void
    {
        $user = Auth::user()?->name ?? 'system';

        // $error_kompartemen_id = '';
        // $error_departemen_id = '';
        // $error_job_role_id = '';

        $data = $row;

        // // --- Begin error/warning logic ---
        // if (!empty($row['kompartemen_id'])) {
        //     $kompartemen = Kompartemen::find($row['kompartemen_id']);
        //     if (!$kompartemen) {
        //         $row['_row_errors'][] = 'Kompartemen ID tidak ada dalam database.';
        //         $error_kompartemen_id = $kompartemen;
        //     }
        // }
        // if (!empty($row['departemen_id'])) {
        //     $departemen = Departemen::find($row['departemen_id']);
        //     if (!$departemen) {
        //         $row['_row_errors'][] = 'Departemen ID tidak ada dalam database.';
        //         $error_departemen_id = $departemen;
        //     }
        // }
        // if (!empty($row['job_role_id'])) {
        //     $jobRole = JobRole::find($row['job_role_id']);
        //     if (!$jobRole) {
        //         $row['_row_errors'][] = 'Job Role ID tidak ada dalam database.';
        //         $error_job_role_id = $jobRole;
        //     }
        // }
        // if (!empty($row['periode_id'])) {
        //     $periode = Periode::find($row['periode_id']);
        //     if (!$periode) {
        //         $row['_row_errors'][] = 'Periode ID tidak ada dalam database.';
        //     }
        // }

        // recap errors and warnings
        $errors = $row['_row_errors'] ?? [];
        $warnings = $row['_row_warnings'] ?? [];

        $keterangan_flagged = '';
        if (!empty($errors)) {
            $keterangan_flagged .= "Errors:\n";
            foreach ($errors as $i => $err) {
                $keterangan_flagged .= ($i + 1) . ". $err\n";
            }
        }
        if (!empty($warnings)) {
            $keterangan_flagged .= "Warnings:\n";
            foreach ($warnings as $i => $warn) {
                $keterangan_flagged .= ($i + 1) . ". $warn\n";
            }
        }
        // Compose keterangan_flagged and error_kompartemen_id
        $keterangan_flagged = trim($keterangan_flagged);

        $flagged = (!empty($errors) || !empty($warnings));

        // $keterangan_flagged = implode("\n", $warnings);

        // $flagged = (!empty($warnings) || !empty($errors));

        // --- End error/warning logic ---

        // Prepare data for update or create
        userGeneric::updateOrCreate(
            [
                'user_code' => $data['user_code'] ?? null,
            ],
            [
                'periode_id' => $data['periode_id'] ?? null,
                'group' => $data['group'] ?? null,
                'user_type' => $data['user_type'] ?? null,
                'cost_code' => $data['cost_code'] ?? null,
                'user_profile' => $data['user_profile'] ?? null,
                'nik' => $data['nik'] ?? null,
                'license_type' => $data['license_type'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'user_listed' => $data['user_listed'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_to' => $data['valid_to'] ?? null,
                'last_login' => $data['last_login'] ?? null,
                'flagged' => $flagged ?? false,
                'keterangan_flagged' => $keterangan_flagged ?? null,
                'created_by' => $user,
                'updated_by' => $user,
            ]
        );
    }
}
