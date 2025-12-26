<?php

namespace App\Services;

use App\Models\ConfigAporteSs;
use App\Models\Empleado;
use App\Models\NominaConcepto;
use App\Models\NominaDetalle;

class NominaAportesSsService
{
    public const CONCEPTOS_EMPLEADO = [
        'TSS-EMP' => 'Aporte TSS (Empleado)',
        'INFOTEP-EMP' => 'Aporte INFOTEP (Empleado)',
        'IDOPPRIL-EMP' => 'Aporte IDOPPRIL (Empleado)',
    ];
    public const CONCEPTOS_EMPLEADOR = [
        'TSS-EMPRESA' => 'Aporte TSS (Empleador)',
        'INFOTEP-EMPRESA' => 'Aporte INFOTEP (Empleador)',
        'IDOPPRIL-EMPRESA' => 'Aporte IDOPPRIL (Empleador)',
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

    public function baseImponibleIsr(Empleado $empleado, int $creatorId, int $periodoId): float
    {
        return $this->baseImponibleDesdeSalario($empleado, $creatorId, 'aplica_isr', $periodoId);
    }

    public function baseImponibleTss(Empleado $empleado, int $creatorId, int $periodoId): float
    {
        return $this->baseImponibleDesdeSalario($empleado, $creatorId, 'aplica_tss', $periodoId);
    }

    public function registrarAportesEmpleado(int $periodoId, Empleado $empleado, int $creatorId): array
    {
        $config = $this->getConfig($creatorId);
        $this->asegurarConceptos($creatorId);

        $baseImponibleTss = $this->baseImponibleTss($empleado, $creatorId, $periodoId);
        $baseImponibleIsr = $this->baseImponibleIsr($empleado, $creatorId, $periodoId);
        $aportes = $this->calcularAportes($baseImponibleTss, $config);

        $this->registrarDetalle($periodoId, $empleado->id, $creatorId, 'TSS-EMP', $aportes['tss_empleado']);
        $this->registrarDetalle($periodoId, $empleado->id, $creatorId, 'INFOTEP-EMP', $aportes['infotep_empleado']);
        $this->registrarDetalle($periodoId, $empleado->id, $creatorId, 'IDOPPRIL-EMP', $aportes['idoppril_empleado']);
        $this->registrarDetalle($periodoId, $empleado->id, $creatorId, 'TSS-EMPRESA', $aportes['tss_empleador']);
        $this->registrarDetalle($periodoId, $empleado->id, $creatorId, 'INFOTEP-EMPRESA', $aportes['infotep_empleador']);
        $this->registrarDetalle($periodoId, $empleado->id, $creatorId, 'IDOPPRIL-EMPRESA', $aportes['idoppril_empleador']);

        return [
            'base_imponible_isr' => $baseImponibleIsr,
            'base_imponible_tss' => $baseImponibleTss,
            'aportes' => $aportes,
        ];
    }

    public function exportarAportes(int $periodoId, int $creatorId): \Illuminate\Support\Collection
    {
        $empleados = \App\Models\Empleado::where('created_by', $creatorId)->get();
        $config = $this->getConfig($creatorId);
        $conceptosIsr = $this->montoConceptosIsr($creatorId, $periodoId);
        $conceptosTss = $this->montoConceptosTss($creatorId, $periodoId);

        return $empleados->map(function ($empleado) use ($creatorId, $config, $conceptosIsr, $conceptosTss) {
            $salario = (float) $empleado->salario;
            $baseImponibleIsr = $salario + $conceptosIsr;
            $baseImponibleTss = $salario + $conceptosTss;
            $aportes = $this->calcularAportes($baseImponibleTss, $config);

            return [
                'empleado' => $empleado->nombre_completo,
                'empleado_model' => $empleado,
                'salario' => $salario,
                'conceptos_isr' => $conceptosIsr,
                'conceptos_tss' => $conceptosTss,
                'base_imponible_isr' => $baseImponibleIsr,
                'base_imponible_tss' => $baseImponibleTss,
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

    private function baseImponibleDesdeSalario(Empleado $empleado, int $creatorId, string $campo, int $periodoId): float
    {
        $montoConceptos = $this->montoConceptosPorPeriodo($creatorId, $periodoId, $campo);

        return (float) $empleado->salario + (float) $montoConceptos;
    }

    public function montoConceptosIsr(int $creatorId, int $periodoId): float
    {
        return (float) $this->montoConceptosPorPeriodo($creatorId, $periodoId, 'aplica_isr');
    }

    public function montoConceptosTss(int $creatorId, int $periodoId): float
    {
        return (float) $this->montoConceptosPorPeriodo($creatorId, $periodoId, 'aplica_tss');
    }

    private function montoConceptosPorPeriodo(int $creatorId, int $periodoId, string $campo): float
    {
        return (float) NominaConcepto::where('created_by', $creatorId)
            ->where($campo, true)
            ->where(function ($query) use ($periodoId) {
                $query->whereNull('nomina_periodo_id')
                    ->orWhere('nomina_periodo_id', $periodoId);
            })
            ->sum('monto');
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
        foreach (self::CONCEPTOS_EMPLEADOR as $codigo => $nombre) {
            NominaConcepto::firstOrCreate(
                [
                    'codigo' => $codigo,
                    'created_by' => $creatorId,
                ],
                [
                    'nombre' => $nombre,
                    'tipo' => 'aporte',
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
