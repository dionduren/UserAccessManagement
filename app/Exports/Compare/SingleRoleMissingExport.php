<?php

namespace App\Exports\Compare;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleRoleMissingExport implements FromCollection, WithHeadings, WithTitle
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
                'Source' => 'Local Only',
            ];
        }

        foreach ($this->middleMissing as $row) {
            $data[] = [
                'Company' => $row['company'],
                'ID' => $row['id'],
                'Description / Value' => $row['value'],
                'Source' => 'Middle Only',
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
            'Source',
        ];
    }

    public function title(): string
    {
        return 'Single Role Missing Comparison';
    }
}
