<?php

namespace App\Imports;

use App\Models\NIKJobRole;
use App\Models\UserNIK;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NIKJobRoleImport implements ToModel, WithHeadingRow, WithChunkReading
{
  use Importable;

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

    return new NIKJobRole([
      'nik' => $row['nik'],
      'job_role' => $row['job_role'],
    ]);
  }
}
