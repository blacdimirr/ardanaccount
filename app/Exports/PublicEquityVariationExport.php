<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublicEquityVariationExport implements FromArray, WithHeadings, WithColumnWidths
{
    public function __construct(private array $report, private string $startDate, private string $endDate, private string $companyName)
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
            [__('Equity Variation Statement') . ' - ' . $this->startDate . ' / ' . $this->endDate, '', ''],
            ['', '', ''],
        ];

        foreach (['increase', 'decrease'] as $sectionKey) {
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

        $rows[] = [__('Net Change in Equity'), '', $this->report['totals']['net_change'] ?? 0];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 45,
            'C' => 20,
        ];
    }
}
