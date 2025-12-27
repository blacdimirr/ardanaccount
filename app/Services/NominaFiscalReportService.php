<?php

namespace App\Services;

use App\Models\NominaConcepto;
use App\Models\NominaDetalle;
use Illuminate\Support\Collection;
use App\Services\NominaIsrService;

class NominaFiscalReportService
{
    public function generarIr3(int $mes, int $anio, int $creatorId): Collection
    {
        $conceptoId = $this->getIsrConceptoId($creatorId);

        if (!$conceptoId) {
            return collect();
        }

        $detalles = NominaDetalle::query()
            ->selectRaw('empleado_id, SUM(monto) as isr_total')
            ->where('created_by', $creatorId)
            ->where('nomina_concepto_id', $conceptoId)
            ->whereHas('periodo', function ($query) use ($mes, $anio) {
                $query->whereYear('fecha_inicio', $anio)
                    ->whereMonth('fecha_inicio', $mes);
            })
            ->groupBy('empleado_id')
            ->with('empleado')
            ->get();

        return $detalles->map(function ($detalle) {
            return $this->mapDetalle($detalle);
        });
    }

    public function generarIr4(int $anio, int $creatorId): Collection
    {
        $conceptoId = $this->getIsrConceptoId($creatorId);

        if (!$conceptoId) {
            return collect();
        }

        $detalles = NominaDetalle::query()
            ->selectRaw('empleado_id, SUM(monto) as isr_total')
            ->where('created_by', $creatorId)
            ->where('nomina_concepto_id', $conceptoId)
            ->whereHas('periodo', function ($query) use ($anio) {
                $query->whereYear('fecha_inicio', $anio);
            })
            ->groupBy('empleado_id')
            ->with('empleado')
            ->get();

        return $detalles->map(function ($detalle) {
            return $this->mapDetalle($detalle);
        });
    }

    private function getIsrConceptoId(int $creatorId): ?int
    {
        return NominaConcepto::where('codigo', NominaIsrService::CONCEPTO_ISR)
            ->where('created_by', $creatorId)
            ->value('id');
    }

    private function mapDetalle(NominaDetalle $detalle): array
    {
        $empleado = $detalle->empleado;

        return [
            'empleado_id' => $detalle->empleado_id,
            'documento' => $empleado?->documento_identidad ?? '',
            'empleado' => $empleado?->nombre_completo ?? '',
            'tipo_contribuyente' => $empleado?->tipo_contribuyente ?? '',
            'isr' => (float) $detalle->isr_total,
        ];
    }
}
