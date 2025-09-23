<?php

namespace App\Exports\Compare;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TcodeMissingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $localMissing;
    protected $middleMissing;

    public function __construct($localMissing, $middleMissing)
    {
        $this->localMissing = $localMissing;
        $this->middleMissing = $middleMissing;
    }

    public function collection()
    {
        return collect($this->localMissing)->merge($this->middleMissing);
    }

    public function headings(): array
    {
        return [
            'Company',
            'ID',
            'Description / Value',
        ];
    }

    public function map($row): array
    {
        return [
            $row['company'],
            $row['id'],
            $row['value'],
        ];
    }
}
