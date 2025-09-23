<?php

namespace App\Exports\Compare;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleRoleExistExport implements FromCollection, WithHeadings, WithTitle
{
    protected $rows;

    public function __construct(array $rows)
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
            'Description / Value',
        ];
    }

    public function title(): string
    {
        return 'Single Role Exist';
    }
}
