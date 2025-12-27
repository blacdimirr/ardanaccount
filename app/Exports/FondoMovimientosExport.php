<?php

namespace App\Exports;

use App\Exports\Concerns\WithCompanyHeader;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FondoMovimientosExport implements FromArray, WithColumnWidths, WithStyles, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

    protected Collection $movimientos;
    protected string $startDate;
    protected string $endDate;
    protected string $fondoNombre;

    public function __construct(Collection $movimientos, string $startDate, string $endDate, string $fondoNombre)
    {
        $this->movimientos = $movimientos;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->fondoNombre = $fondoNombre;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [__('Fund Movements Report')];
        $rows[] = [__('Period'), $this->startDate . ' - ' . $this->endDate];
        $rows[] = [__('Fund'), $this->fondoNombre !== '' ? $this->fondoNombre : __('All')];
        $rows[] = [''];
        $rows[] = [
            __('Date'),
            __('Fund'),
            __('Type'),
            __('Amount'),
            __('Description'),
            __('Receipt/Voucher'),
        ];

        foreach ($this->movimientos as $movimiento) {
            $rows[] = [
                $movimiento->fecha,
                $movimiento->fondo?->nombre,
                $movimiento->tipo === 'egreso' ? __('Expense') : __('Replenishment'),
                $movimiento->monto,
                $movimiento->descripcion,
                $movimiento->comprobante_id,
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 30,
            'C' => 18,
            'D' => 18,
            'E' => 45,
            'F' => 20,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $startRow = $this->companyHeaderStartRow();

        return [
            $startRow => ['font' => ['bold' => true]],
            $startRow + 4 => ['font' => ['bold' => true]],
        ];
    }
}
