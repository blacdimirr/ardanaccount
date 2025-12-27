<?php

namespace App\Exports;

use App\Models\CuentaRecaudadora;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstadosCuentaConciliacionExport implements FromArray, WithColumnWidths, WithStyles
{
    protected string $startDate;
    protected string $endDate;
    protected ?CuentaRecaudadora $cuenta;
    protected array $statementData;
    protected Collection $differenceRows;

    public function __construct(
        string $startDate,
        string $endDate,
        ?CuentaRecaudadora $cuenta,
        array $statementData,
        Collection $differenceRows
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->cuenta = $cuenta;
        $this->statementData = $statementData;
        $this->differenceRows = $differenceRows;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [__('Account Statement & Reconciliation')];
        $rows[] = [__('Period'), $this->startDate . ' - ' . $this->endDate];
        if ($this->cuenta) {
            $rows[] = [__('Account'), $this->cuenta->banco . ' - ' . $this->cuenta->numero_cuenta];
        }
        $rows[] = [''];

        $rows[] = [__('Opening Balance'), $this->statementData['opening_balance']];
        $rows[] = [__('Period Total'), $this->statementData['period_total']];
        $rows[] = [__('Closing Balance'), $this->statementData['closing_balance']];
        $rows[] = [''];

        $rows[] = [__('Statement Movements')];
        $rows[] = [
            __('Date'),
            __('Description'),
            __('Reference'),
            __('Amount'),
            __('Status'),
            __('Matched Record'),
            __('Matched Amount'),
            __('Running Balance'),
            __('Collection Account'),
        ];

        foreach ($this->statementData['rows'] as $row) {
            $movimiento = $row['movimiento'];
            $accountLabel = $movimiento->cuentaRecaudadora
                ? $movimiento->cuentaRecaudadora->banco . ' - ' . $movimiento->cuentaRecaudadora->numero_cuenta
                : '-';

            $rows[] = [
                $movimiento->fecha,
                $movimiento->descripcion,
                $movimiento->referencia,
                $movimiento->monto,
                $movimiento->estado_conciliacion,
                $row['matched_label'],
                $row['matched_amount'] ?? '-',
                $row['saldo'],
                $accountLabel,
            ];
        }

        $rows[] = [''];
        $rows[] = [__('Reconciliation Differences')];
        $rows[] = [
            __('Date'),
            __('Description'),
            __('Reference'),
            __('Amount'),
            __('Status'),
            __('Matched Record'),
            __('Matched Amount'),
            __('Difference'),
        ];

        foreach ($this->differenceRows as $row) {
            $movimiento = $row['movimiento'];
            $rows[] = [
                $movimiento->fecha,
                $movimiento->descripcion,
                $movimiento->referencia,
                $movimiento->monto,
                $movimiento->estado_conciliacion,
                $row['matched_label'],
                $row['matched_amount'] ?? '-',
                $row['difference'],
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 36,
            'C' => 24,
            'D' => 16,
            'E' => 16,
            'F' => 26,
            'G' => 18,
            'H' => 18,
            'I' => 28,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
