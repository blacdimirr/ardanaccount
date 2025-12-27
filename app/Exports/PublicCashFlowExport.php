<?php

namespace App\Exports;

use App\Exports\Concerns\WithCompanyHeader;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublicCashFlowExport implements FromArray, WithHeadings, WithColumnWidths, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

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
            [__('Cash Flow Statement') . ' - ' . $this->startDate . ' / ' . $this->endDate, '', ''],
            ['', '', ''],
        ];

        foreach (['operating', 'investing', 'financing'] as $sectionKey) {
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

        $rows[] = [__('Net Cash Flow'), '', $this->report['totals']['net_cash'] ?? 0];

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
