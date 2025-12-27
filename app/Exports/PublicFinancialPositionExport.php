<?php

namespace App\Exports;

use App\Exports\Concerns\WithCompanyHeader;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublicFinancialPositionExport implements FromArray, WithHeadings, WithColumnWidths, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

    public function __construct(
        private array $report,
        private string $cutoffDate,
        private string $companyName,
        private array $notes = []
    )
    {
    }

    public function headings(): array
    {
        return [
            __('Section'),
            __('Line'),
            __('Total'),
        ];
    }

    public function array(): array
    {
        $rows = [
            [$this->companyName, '', ''],
            [__('Public Financial Position Statement') . ' - ' . $this->cutoffDate, '', ''],
            ['', '', ''],
        ];

        foreach (['assets', 'liabilities', 'equity'] as $sectionKey) {
            $section = $this->report[$sectionKey] ?? null;
            if (!$section) {
                continue;
            }

            $rows[] = [$section['label'], '', ''];
            foreach ($section['lines'] as $line) {
                $rows[] = ['', $line['name'], $line['total']];
            }
            $rows[] = ['', __('Total') . ' ' . $section['label'], $section['total']];
            $rows[] = ['', '', ''];
        }

        $rows[] = [
            __('Total Liabilities & Equity'),
            '',
            $this->report['totals']['liabilities_equity'] ?? 0,
        ];

        if (!empty($this->notes)) {
            $rows[] = ['', '', ''];
            $rows[] = [__('Notes to Financial Statements'), '', ''];
            foreach ($this->notes as $note) {
                $rows[] = [
                    '',
                    trim(($note['codigo_nota'] ?? '') . ' ' . ($note['titulo'] ?? '')),
                    $note['contenido'] ?? '',
                ];
            }
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 40,
            'C' => 20,
        ];
    }
}
