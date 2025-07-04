<?php

namespace App\Services;

use App\Models\UserGenericUnitKerja;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\Periode;
use Illuminate\Support\Facades\Auth;

class UserGenericUnitKerjaService
{
    public function handleRow(array $row): void
    {
        $user = Auth::user()?->name ?? 'system';

        $errors = [];
        $warnings = [];

        if (!empty($row['kompartemen_id']) && !Kompartemen::find($row['kompartemen_id'])) {
            $errors[] = 'Kompartemen ID tidak ada dalam database.';
        }
        if (!empty($row['departemen_id']) && !Departemen::find($row['departemen_id'])) {
            $errors[] = 'Departemen ID tidak ada dalam database.';
        }
        if (!empty($row['periode_id']) && !Periode::find($row['periode_id'])) {
            $errors[] = 'Periode ID tidak ada dalam database.';
        }
        if (empty($row['user_cc'])) {
            $errors[] = 'User CC wajib diisi.';
        }

        $flagged = !empty($errors) || !empty($warnings);
        $keterangan_flagged = '';
        if ($errors) {
            $keterangan_flagged .= "Errors:\n" . implode("\n", $errors) . "\n";
        }
        if ($warnings) {
            $keterangan_flagged .= "Warnings:\n" . implode("\n", $warnings);
        }

        UserGenericUnitKerja::updateOrCreate(
            [
                'user_cc' => $row['user_cc'],
                'periode_id' => $row['periode_id'],
            ],
            [
                'kompartemen_id' => $row['kompartemen_id'] ?? null,
                'departemen_id' => $row['departemen_id'] ?? null,
                'error_kompartemen_id' => $row['error_kompartemen_id'] ?? null,
                'error_departemen_id' => $row['error_departemen_id'] ?? null,
                'flagged' => $flagged,
                'keterangan_flagged' => trim($keterangan_flagged),
                'created_by' => $user,
                'updated_by' => $user,
            ]
        );
    }
}
