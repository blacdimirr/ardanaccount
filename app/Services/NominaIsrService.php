<?php

namespace App\Services;

use App\Models\ConfigIsrHonorario;
use App\Models\ConfigIsrTramo;
use App\Models\Empleado;
use App\Models\NominaConcepto;
use App\Models\NominaDetalle;
use Illuminate\Support\Collection;

class NominaIsrService
{
    public const CONCEPTO_ISR = 'ISR';

    public function getHonorariosConfig(int $creatorId): ConfigIsrHonorario
    {
        return ConfigIsrHonorario::firstOrCreate(
            ['created_by' => $creatorId],
            ['retencion_honorarios' => 0]
        );
    }

    public function getTramos(int $creatorId): Collection
    {
        return ConfigIsrTramo::where('created_by', $creatorId)
            ->orderBy('rango_desde')
            ->get();
    }

    public function calcularIsr(float $baseImponible, Empleado $empleado, int $creatorId): float
    {
        if ($baseImponible <= 0) {
            return 0;
        }

        if ($empleado->tipo_contribuyente === 'honorarios') {
            $config = $this->getHonorariosConfig($creatorId);

            return $this->calcularMonto($baseImponible, $config->retencion_honorarios);
        }

        $tramos = $this->getTramos($creatorId);

        return $this->calcularPorTramos($baseImponible, $tramos);
    }

    public function registrarIsrEmpleado(int $periodoId, Empleado $empleado, int $creatorId, float $baseImponible): float
    {
        $this->asegurarConcepto($creatorId);
        $isr = $this->calcularIsr($baseImponible, $empleado, $creatorId);

        $this->registrarDetalle($periodoId, $empleado, $creatorId, $isr);

        return $isr;
    }

    private function calcularPorTramos(float $baseImponible, Collection $tramos): float
    {
        $isr = 0;

        foreach ($tramos as $tramo) {
            $desde = (float) $tramo->rango_desde;
            $hasta = $tramo->rango_hasta !== null ? (float) $tramo->rango_hasta : null;

            if ($baseImponible <= $desde) {
                break;
            }

            $limite = $hasta ?? $baseImponible;
            $montoTramo = min($baseImponible, $limite) - $desde;

            if ($montoTramo > 0) {
                $isr += $montoTramo * ($tramo->tasa / 100);
            }

            if ($hasta !== null && $baseImponible <= $hasta) {
                break;
            }
        }

        return round($isr, 2);
    }

    private function calcularMonto(float $baseImponible, float $porcentaje): float
    {
        return round($baseImponible * ($porcentaje / 100), 2);
    }

    private function asegurarConcepto(int $creatorId): void
    {
        NominaConcepto::firstOrCreate(
            [
                'codigo' => self::CONCEPTO_ISR,
                'created_by' => $creatorId,
            ],
            [
                'nombre' => 'ISR',
                'tipo' => 'descuento',
                'naturaleza' => 'Impuesto sobre la renta',
                'created_by' => $creatorId,
            ]
        );
    }

    private function registrarDetalle(int $periodoId, Empleado $empleado, int $creatorId, float $monto): void
    {
        $concepto = NominaConcepto::where('codigo', self::CONCEPTO_ISR)
            ->where('created_by', $creatorId)
            ->first();

        if (!$concepto) {
            return;
        }

        NominaDetalle::updateOrCreate(
            [
                'nomina_periodo_id' => $periodoId,
                'empleado_id' => $empleado->id,
                'nomina_concepto_id' => $concepto->id,
                'created_by' => $creatorId,
            ],
            [
                'monto' => $monto,
                'servicio_id' => $empleado->servicio_id,
            ]
        );
    }
}
