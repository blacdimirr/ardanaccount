<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConciliacionBancariaExport implements FromArray, WithColumnWidths, WithStyles
{
    protected Collection $movimientos;
    protected string $startDate;
    protected string $endDate;
    protected array $totalsByStatus;

    public function __construct(Collection $movimientos, string $startDate, string $endDate, array $totalsByStatus)
    {
        $this->movimientos = $movimientos;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->totalsByStatus = $totalsByStatus;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [__('Bank Reconciliation Report')];
        $rows[] = [__('Period'), $this->startDate . ' - ' . $this->endDate];
        $rows[] = [''];

        $rows[] = [__('Totals by Status')];
        foreach ($this->totalsByStatus as $label => $total) {
            $rows[] = [$label, $total];
        }
        $rows[] = [''];

        $rows[] = [
            __('Date'),
            __('Description'),
            __('Reference'),
            __('Amount'),
            __('Collection Account'),
            __('Status'),
            __('Matched Record'),
        ];

        foreach ($this->movimientos as $movimiento) {
            $accountLabel = '-';
            if ($movimiento->cuentaRecaudadora) {
                $accountLabel = $movimiento->cuentaRecaudadora->banco . ' - ' . $movimiento->cuentaRecaudadora->numero_cuenta;
            }

            $matchedLabel = '-';
            if ($movimiento->conciliable) {
                $matchedLabel = class_basename($movimiento->conciliable_type) . ' #' . $movimiento->conciliable->id;
            }

            $rows[] = [
                $movimiento->fecha,
                $movimiento->descripcion,
                $movimiento->referencia,
                $movimiento->monto,
                $accountLabel,
                $movimiento->estado_conciliacion,
                $matchedLabel,
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 40,
            'C' => 25,
            'D' => 16,
            'E' => 35,
            'F' => 16,
            'G' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
