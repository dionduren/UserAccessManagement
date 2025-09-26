<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UserGenericPreviewImport implements WithMultipleSheets, SkipsUnknownSheets
{
    use Importable;

    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function sheets(): array
    {
        return [
            'UPLOAD_TEMPLATE' => new class($this) implements ToCollection, WithHeadingRow {
                public function __construct(private UserGenericPreviewImport $parent) {}

                public function collection(Collection $rows)
                {
                    foreach ($rows as $row) {
                        $mappedRow = [
                            'group'         => $row['group'] ?? null,
                            'user_code'     => $row['user_code'] ?? null,
                            'user_type'     => $row['user_type'] ?? null,
                            'user_profile'  => $row['user_profile'] ?? null,
                            'nik'           => $row['nik'] ?? null,
                            'cost_code'     => $row['cost_code'] ?? null,
                            'license_type'  => $row['license_type'] ?? null,
                            'last_login'    => $row['last_login'] ?? null,
                            'valid_from'    => $row['valid_from'] ?? null,
                            'valid_to'      => $row['valid_to'] ?? null,
                            'keterangan'    => $row['keterangan'] ?? null,
                            'uar_listed'    => $row['uar_listed'] ?? null,
                            'created_by'    => auth()->id(),
                        ];

                        $this->parent->rows->push(collect($mappedRow));
                    }
                }
            },
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // ignore other sheets
    }
}
