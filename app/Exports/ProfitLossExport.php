<?php

namespace App\Exports;

use App\Exports\Concerns\WithCompanyHeader;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProfitLossExport implements FromArray, WithEvents, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader {
        registerEvents as registerCompanyHeaderEvents;
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($data , $startDate, $endDate, $companyName, array $notes = [])
    {
        $formattedData = [];
        $totalIncome = 0; $totalCosts = 0; $totalExpense =0;

        foreach ($data  as $category)
        {
            if($category['Type'] == 'Income' || $category['Type'] == 'Costs of Goods Sold')
            {

            $formattedData[] = [
                'Account Name' => '',
                'Account No'   => '',
                'Total'        => ''
            ];

            $formattedData[] = [
                'Account Name' => $category['Type'],
                'Account No'   => '',
                'Total'        => ''
            ];

                foreach($category['account'] as $accounts)
                {
                    foreach($accounts as $account)
                    {
                    if($account['netAmount'] > 0)
                    {
                        $netAmount = $account['netAmount'];
                    }
                    else
                    {
                        $netAmount = -$account['netAmount'];
                    }
                    if ($account['account'] == 'parent' || $account['account'] == 'parentTotal')
                    {
                        $formattedData[] = [
                            'Account Name' => '   ' . $account['account_name'],
                            'Account No'   => $account['account_code'],
                            'Total'        => $netAmount
                        ];
                    }
                    elseif (!preg_match('/\btotal\b/i', $account['account_name']) || $account['account'] == 'subAccount')
                    {
                        $formattedData[] = [
                            'Account Name' => '     ' . $account['account_name'],
                            'Account No'   => $account['account_code'],
                            'Total'        => $netAmount
                        ];
                    }
                    else
                    {
                        $formattedData[] = [
                            'Account Name' => $account['account_name'],
                            'Account No'   => $account['account_code'],
                            'Total'        => $netAmount
                        ];
                    }
    
                    if($account['account_name'] == 'Total Income')
                    {
                        $totalIncome = $netAmount;
                    }
    
                    if($account['account_name'] == 'Total Costs of Goods Sold')
                    {
                        $totalCosts = $netAmount;
                    }
                }
            }
            
            }
        }


        $grossProfit = $totalIncome - $totalCosts;
        
        $formattedData[] = [
            'Account Name' => 'Gross Profit',
            'Account No'   => '',
            'Total'        => $grossProfit
        ];

        foreach ($data  as $category)
        {

            if($category['Type'] == 'Expenses')
            {
            $formattedData[] = [
                'Account Name' => '',
                'Account No'   => '',
                'Total'        => ''
            ];

            $formattedData[] = [
                'Account Name' => $category['Type'],
                'Account No'   => '',
                'Total'        => ''
            ];

            foreach($category['account'] as $accounts)
            {
                foreach($accounts as $account)
                {
                if($account['netAmount'] > 0)
                {
                    $netAmount = $account['netAmount'];
                }
                else
                {
                    $netAmount = -$account['netAmount'];
                }


                if ($account['account'] == 'parent' || $account['account'] == 'parentTotal')
                {
                    $formattedData[] = [
                        'Account Name' => '   ' . $account['account_name'],
                        'Account No'   => $account['account_code'],
                        'Total'        => $netAmount
                    ];
                }
                elseif (!preg_match('/\btotal\b/i', $account['account_name']) || $account['account'] == 'subAccount')
                {
                    $formattedData[] = [
                        'Account Name' => '     ' . $account['account_name'],
                        'Account No'   => $account['account_code'],
                        'Total'        => $netAmount
                    ];
                }
                else
                {
                    $formattedData[] = [
                        'Account Name' => $account['account_name'],
                        'Account No'   => $account['account_code'],
                        'Total'        => $netAmount
                    ];
                }
            }
            }

            $formattedData[] = [
                'Account Name' => 'Net Profit/Loss',
                'Account No'   => '',
                'Total'        => $grossProfit -  $totalExpense
            ];
        }
        }

        if (!empty($notes)) {
            $formattedData[] = [
                'Account Name' => '',
                'Account No' => '',
                'Total' => '',
            ];
            $formattedData[] = [
                'Account Name' => __('Notes to Financial Statements'),
                'Account No' => '',
                'Total' => '',
            ];
            foreach ($notes as $note) {
                $formattedData[] = [
                    'Account Name' => trim(($note['codigo_nota'] ?? '') . ' ' . ($note['titulo'] ?? '')),
                    'Account No' => '',
                    'Total' => $note['contenido'] ?? '',
                ];
            }
        }
        $this->data        = $formattedData;
        $this->companyName = $companyName;
        $this->startDate   = $startDate;
        $this->endDate     = $endDate;
    }

    public function startCell(): string
    {
        return 'A9';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A9')->getFont()->setBold(true);
        $sheet->getStyle('B9')->getFont()->setBold(true);
        $sheet->getStyle('C9')->getFont()->setBold(true);
        $sheet->getStyle('D9')->getFont()->setBold(true);
        $sheet->getStyle('E9')->getFont()->setBold(true);
        $sheet->getStyle('F9')->getFont()->setBold(true);
    }

    public function array(): array
    {
        return $this->data ;
    }


    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {

            },

            AfterSheet::class => function (AfterSheet $event) {
                $this->registerCompanyHeaderEvents()[AfterSheet::class]($event);

                $event->sheet->getDelegate()->mergeCells('A5:F5');
                $event->sheet->getDelegate()->mergeCells('A6:F6');
                $event->sheet->getDelegate()->mergeCells('A7:F7');

                $event->sheet->getDelegate()->setCellValue('A5', 'Estado de resultados - ' . $this->companyName)->getStyle('A5')->getFont()->setBold(true);
                $event->sheet->getDelegate()->setCellValue('A6', 'Fecha de impresión: ' . date('d/m/Y H:i'));
                $event->sheet->getDelegate()->setCellValue('A7', 'Periodo: ' . $this->startDate . ' - ' . $this->endDate);

                $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->getDelegate()->getStyle('A')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle('B')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $data = $this->data;
                foreach ($data as $index => $row) {

                    if (isset($row['Account Name']) && ($row['Account Name'] == 'Total Costs of Goods Sold' || $row['Account Name'] == 'Total Income' || $row['Account Name'] == 'Income'
                       || $row['Account Name'] == 'Costs of Goods Sold' || $row['Account Name'] == 'Expenses' || $row['Account Name'] == 'Total Expenses')) {
                        $rowIndex = $index + 10; // Adjust for 1-based indexing and header row
                        $event->sheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                ],

                            ]);
                    }
                    elseif(isset($row['Account Name']) && ($row['Account Name'] == 'Gross Profit' || $row['Account Name'] == 'Net Profit/Loss'))
                    {
                        $rowIndex = $index + 10; // Adjust for 1-based indexing and header row
                        $event->sheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                ],
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                ],
                            ]);
                        $event->sheet->mergeCells('A' . $rowIndex . ':B' . $rowIndex);

                    }
                }

            },
        ];
    }

    public function headings(): array
    {
        return [
            "Account",
            "Account No",
            "Total",
        ];
    }
}
