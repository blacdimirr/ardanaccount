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

class RecaudacionesDiariasExport implements FromArray, WithColumnWidths, WithStyles, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

    protected Collection $recaudaciones;
    protected string $startDate;
    protected string $endDate;
    protected array $totalsByService;
    protected array $totalsByMethod;
    protected array $totalsByAccount;

    public function __construct(
        Collection $recaudaciones,
        string $startDate,
        string $endDate,
        array $totalsByService,
        array $totalsByMethod,
        array $totalsByAccount
    ) {
        $this->recaudaciones = $recaudaciones;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->totalsByService = $totalsByService;
        $this->totalsByMethod = $totalsByMethod;
        $this->totalsByAccount = $totalsByAccount;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [__('Daily Collections Report')];
        $rows[] = [__('Period'), $this->startDate . ' - ' . $this->endDate];
        $rows[] = [''];

        $rows[] = [__('Totals by Service')];
        foreach ($this->totalsByService as $label => $total) {
            $rows[] = [$label, $total];
        }
        $rows[] = [''];

        $rows[] = [__('Totals by Payment Method')];
        foreach ($this->totalsByMethod as $label => $total) {
            $rows[] = [$label, $total];
        }
        $rows[] = [''];

        $rows[] = [__('Totals by Collection Account')];
        foreach ($this->totalsByAccount as $label => $total) {
            $rows[] = [$label, $total];
        }
        $rows[] = [''];

        $rows[] = [
            __('Date'),
            __('Service'),
            __('Amount'),
            __('Payment Method'),
            __('Collection Account'),
            __('Patient'),
        ];

        foreach ($this->recaudaciones as $recaudacion) {
            $accountLabel = '-';
            if ($recaudacion->cuentaRecaudadora) {
                $accountLabel = $recaudacion->cuentaRecaudadora->banco . ' - ' . $recaudacion->cuentaRecaudadora->numero_cuenta;
            }

            $rows[] = [
                $recaudacion->fecha,
                $recaudacion->servicio,
                $recaudacion->monto,
                $recaudacion->metodo_pago,
                $accountLabel,
                $recaudacion->paciente_id,
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 30,
            'C' => 18,
            'D' => 24,
            'E' => 35,
            'F' => 18,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $startRow = $this->companyHeaderStartRow();

        return [
            $startRow => ['font' => ['bold' => true]],
            $startRow + 3 => ['font' => ['bold' => true]],
        ];
    }
}
