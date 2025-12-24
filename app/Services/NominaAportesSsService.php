<?php

namespace App\Services;

use App\Models\ConfigAporteSs;
use App\Models\NominaConcepto;
use App\Models\NominaDetalle;

class NominaAportesSsService
{
    public const CONCEPTOS_EMPLEADO = [
        'TSS-EMP' => 'Aporte TSS (Empleado)',
        'INFOTEP-EMP' => 'Aporte INFOTEP (Empleado)',
        'IDOPPRIL-EMP' => 'Aporte IDOPPRIL (Empleado)',
    ];

    public function getConfig(int $creatorId): ConfigAporteSs
    {
        return ConfigAporteSs::firstOrCreate(
            ['created_by' => $creatorId],
            [
                'tss_empleador' => 0,
                'tss_empleado' => 0,
                'infotep_empleador' => 0,
                'infotep_empleado' => 0,
                'idoppril_empleador' => 0,
                'idoppril_empleado' => 0,
            ]
        );
    }

    public function calcularAportes(float $baseImponible, ConfigAporteSs $config): array
    {
        $tssEmpleado = $this->calcularMonto($baseImponible, $config->tss_empleado);
        $infotepEmpleado = $this->calcularMonto($baseImponible, $config->infotep_empleado);
        $idopprilEmpleado = $this->calcularMonto($baseImponible, $config->idoppril_empleado);

        $tssEmpleador = $this->calcularMonto($baseImponible, $config->tss_empleador);
        $infotepEmpleador = $this->calcularMonto($baseImponible, $config->infotep_empleador);
        $idopprilEmpleador = $this->calcularMonto($baseImponible, $config->idoppril_empleador);

        return [
            'tss_empleado' => $tssEmpleado,
            'infotep_empleado' => $infotepEmpleado,
            'idoppril_empleado' => $idopprilEmpleado,
            'tss_empleador' => $tssEmpleador,
            'infotep_empleador' => $infotepEmpleador,
            'idoppril_empleador' => $idopprilEmpleador,
        ];
    }

    public function baseImponible(int $periodoId, int $empleadoId, int $creatorId): float
    {
        return (float) NominaDetalle::query()
            ->where('created_by', $creatorId)
            ->where('nomina_periodo_id', $periodoId)
            ->where('empleado_id', $empleadoId)
            ->whereHas('concepto', function ($query) {
                $query->where('tipo', 'ingreso');
            })
            ->sum('monto');
    }

    public function registrarAportesEmpleado(int $periodoId, int $empleadoId, int $creatorId): array
    {
        $config = $this->getConfig($creatorId);
        $this->asegurarConceptos($creatorId);

        $baseImponible = $this->baseImponible($periodoId, $empleadoId, $creatorId);
        $aportes = $this->calcularAportes($baseImponible, $config);

        $this->registrarDetalle($periodoId, $empleadoId, $creatorId, 'TSS-EMP', $aportes['tss_empleado']);
        $this->registrarDetalle($periodoId, $empleadoId, $creatorId, 'INFOTEP-EMP', $aportes['infotep_empleado']);
        $this->registrarDetalle($periodoId, $empleadoId, $creatorId, 'IDOPPRIL-EMP', $aportes['idoppril_empleado']);

        return [
            'base_imponible' => $baseImponible,
            'aportes' => $aportes,
        ];
    }

    public function exportarAportes(int $periodoId, int $creatorId): \Illuminate\Support\Collection
    {
        $empleados = \App\Models\Empleado::where('created_by', $creatorId)->get();
        $config = $this->getConfig($creatorId);

        return $empleados->map(function ($empleado) use ($periodoId, $creatorId, $config) {
            $baseImponible = $this->baseImponible($periodoId, $empleado->id, $creatorId);
            $aportes = $this->calcularAportes($baseImponible, $config);

            return [
                'empleado' => $empleado->nombre_completo,
                'empleado_model' => $empleado,
                'base_imponible' => $baseImponible,
                'tss_empleado' => $aportes['tss_empleado'],
                'infotep_empleado' => $aportes['infotep_empleado'],
                'idoppril_empleado' => $aportes['idoppril_empleado'],
                'tss_empleador' => $aportes['tss_empleador'],
                'infotep_empleador' => $aportes['infotep_empleador'],
                'idoppril_empleador' => $aportes['idoppril_empleador'],
            ];
        });
    }

    private function calcularMonto(float $baseImponible, float $porcentaje): float
    {
        return round($baseImponible * ($porcentaje / 100), 2);
    }

    private function asegurarConceptos(int $creatorId): void
    {
        foreach (self::CONCEPTOS_EMPLEADO as $codigo => $nombre) {
            NominaConcepto::firstOrCreate(
                [
                    'codigo' => $codigo,
                    'created_by' => $creatorId,
                ],
                [
                    'nombre' => $nombre,
                    'tipo' => 'descuento',
                    'naturaleza' => 'Aportes seguridad social',
                    'created_by' => $creatorId,
                ]
            );
        }
    }

    private function registrarDetalle(int $periodoId, int $empleadoId, int $creatorId, string $codigoConcepto, float $monto): void
    {
        $concepto = NominaConcepto::where('codigo', $codigoConcepto)
            ->where('created_by', $creatorId)
            ->first();

        if (!$concepto) {
            return;
        }

        NominaDetalle::updateOrCreate(
            [
                'nomina_periodo_id' => $periodoId,
                'empleado_id' => $empleadoId,
                'nomina_concepto_id' => $concepto->id,
                'created_by' => $creatorId,
            ],
            [
                'monto' => $monto,
            ]
        );
    }
}
