<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ExcelChunkReadFilter implements IReadFilter
{
    private int $startRow = 0;
    private int $endRow = 0;

    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize - 1;
    }

    public function readCell($column, $row, $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}
