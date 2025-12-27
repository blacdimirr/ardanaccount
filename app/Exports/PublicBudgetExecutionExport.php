<?php

namespace App\Exports;

use App\Exports\Concerns\WithCompanyHeader;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublicBudgetExecutionExport implements FromArray, WithColumnWidths, WithStyles, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

    protected array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 18,
            'C' => 18,
            'D' => 18,
            'E' => 18,
            'F' => 18,
            'G' => 18,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $startRow = $this->companyHeaderStartRow();

        return [
            $startRow => ['font' => ['bold' => true]],
            $startRow + 5 => ['font' => ['bold' => true]],
        ];
    }
}
