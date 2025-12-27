<?php

namespace App\Exports;

use App\Models\NominaPeriodo;
use App\Exports\Concerns\WithCompanyHeader;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NominaCostosServicioExport implements FromArray, WithHeadings, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

    private array $services;
    private array $totals;
    private ?NominaPeriodo $periodo;

    public function __construct(array $services, array $totals, ?NominaPeriodo $periodo)
    {
        $this->services = $services;
        $this->totals = $totals;
        $this->periodo = $periodo;
    }

    public function headings(): array
    {
        return [
            __('Servicio/Unidad'),
            __('Gastos'),
            __('Descuentos'),
            __('Neto'),
        ];
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->services as $service) {
            $data[] = [
                $service['servicio'],
                $service['gastos'],
                $service['descuentos'],
                $service['neto'],
            ];
        }

        $data[] = [
            __('Totales'),
            $this->totals['gastos'] ?? 0,
            $this->totals['descuentos'] ?? 0,
            $this->totals['neto'] ?? 0,
        ];

        return $data;
    }
}
