<?php

namespace App\Exports;

use App\Services\NominaAportesSsService;
use App\Services\NominaIsrService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NominaAportesExport implements FromCollection, WithHeadings
{
    private int $periodoId;
    private int $creatorId;
    private NominaAportesSsService $service;
    private NominaIsrService $isrService;

    public function __construct(int $periodoId, int $creatorId, NominaAportesSsService $service, NominaIsrService $isrService)
    {
        $this->periodoId = $periodoId;
        $this->creatorId = $creatorId;
        $this->service = $service;
        $this->isrService = $isrService;
    }

    public function collection(): Collection
    {
        return collect($this->service->exportarAportes($this->periodoId, $this->creatorId))
            ->map(function ($row) {
                $isr = $this->isrService->calcularIsr($row['base_imponible'], $row['empleado_model'], $this->creatorId);

                return [
                    $row['empleado'],
                    $row['base_imponible'],
                    $isr,
                    $row['tss_empleado'],
                    $row['infotep_empleado'],
                    $row['idoppril_empleado'],
                    $row['tss_empleador'],
                    $row['infotep_empleador'],
                    $row['idoppril_empleador'],
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Empleado',
            'Base imponible',
            'ISR',
            'TSS (Empleado)',
            'INFOTEP (Empleado)',
            'IDOPPRIL (Empleado)',
            'TSS (Empleador)',
            'INFOTEP (Empleador)',
            'IDOPPRIL (Empleador)',
        ];
    }
}
