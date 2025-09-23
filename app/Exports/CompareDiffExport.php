<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompareDiffExport implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $rows;
    protected string $title;
    protected array $headings;

    public function __construct(array $rows, string $title, array $headings = ['Company', 'ID', 'Description / Value'])
    {
        $this->rows     = collect($rows)->map(fn($r) => [
            $r['company'] ?? '',
            $r['id'] ?? '',
            $r['value'] ?? ''
        ]);
        $this->title    = $title;
        $this->headings = $headings;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
