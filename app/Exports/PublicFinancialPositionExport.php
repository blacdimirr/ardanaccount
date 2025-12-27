<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublicFinancialPositionExport implements FromArray, WithHeadings, WithColumnWidths
{
    public function __construct(private array $report, private string $cutoffDate, private string $companyName)
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
