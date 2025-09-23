<?php

namespace App\Exports\Compare;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CompositeRoleExistExport implements FromCollection, WithHeadings, WithMapping
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'Company',
            'ID',
            'Description',
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
