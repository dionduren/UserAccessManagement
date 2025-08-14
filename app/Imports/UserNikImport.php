<?php

namespace App\Imports;

use App\Models\userNIK;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserNikImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new userNIK([
            'group' => $row['group'],
            'user_code' => $row['user_code'],
            'user_type' => $row['user_type'],
            'license_type' => $row['license_type'],
            'last_login' => empty($row['last_login']) ? null : Carbon::parse($row['last_login']),
            'valid_from' => empty($row['valid_from']) ? null : Carbon::parse($row['valid_from']),
            'valid_to' => empty($row['valid_to']) ? null : Carbon::parse($row['valid_to']),
        ]);
    }
}
