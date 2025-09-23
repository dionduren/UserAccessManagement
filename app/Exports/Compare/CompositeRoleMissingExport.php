<?php

namespace App\Exports\Compare;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompositeRoleMissingExport implements FromCollection, WithHeadings, WithTitle
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
        $data = [];

        foreach ($this->localMissing as $row) {
            $data[] = [
                'Company' => $row['company'],
                'ID' => $row['id'],
                'Description / Value' => $row['value'],
            ];
        }

        foreach ($this->middleMissing as $row) {
            $data[] = [
                'Company' => $row['company'],
                'ID' => $row['id'],
                'Description / Value' => $row['value'],
            ];
        }

        return collect($data);
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
        return 'Missing Composite Roles';
    }
}
