<?php

namespace App\Services;

use App\Models\MovimientoBancario;
use App\Models\Payment;
use App\Models\Recaudacion;
use App\Models\ReglaConciliacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ConciliacionBancariaService
{
    public function conciliarAutomaticamente(Collection $movimientos): array
    {
        $rules = $this->resolveRules();
        $conciliados = 0;
        $parciales = 0;
        $pendientes = 0;

        foreach ($movimientos as $movimiento) {
            if ($movimiento->estado_conciliacion !== 'pendiente') {
                continue;
            }

            $registro = $this->findMatch($movimiento, $rules);
            if (!$registro) {
                $pendientes++;
                continue;
            }

            $estado = $this->conciliarMovimiento($movimiento, $registro);
            if ($estado === 'parcial') {
                $parciales++;
            } else {
                $conciliados++;
            }
        }

        return [
            'conciliados' => $conciliados,
            'parciales' => $parciales,
            'pendientes' => $pendientes,
        ];
    }

    public function conciliarMovimiento(MovimientoBancario $movimiento, Model $registro): string
    {
        $amount = $this->getRegistroMonto($registro);
        $diferencia = abs($movimiento->monto - $amount);
        $estado = $diferencia > 0 ? 'parcial' : 'conciliado';

        $movimiento->estado_conciliacion = $estado;
        $movimiento->conciliable()->associate($registro);
        $movimiento->save();

        $registro->estado_conciliacion = $estado;
        $registro->save();

        return $estado;
    }

    private function resolveRules(): Collection
    {
        $rules = ReglaConciliacion::query()->where('activo', true)->orderBy('id')->get();

        if ($rules->isEmpty()) {
            return collect([
                new ReglaConciliacion([
                    'nombre' => 'Regla por defecto',
                    'usar_referencia' => false,
                    'usar_monto' => true,
                    'usar_fecha' => true,
                    'tolerancia_monto' => 0,
                    'rango_dias' => 2,
                ]),
            ]);
        }

        return $rules;
    }

    private function findMatch(MovimientoBancario $movimiento, Collection $rules): ?Model
    {
        foreach ($rules as $rule) {
            $recaudacion = $this->matchRecaudacion($movimiento, $rule);
            if ($recaudacion) {
                return $recaudacion;
            }

            $payment = $this->matchPayment($movimiento, $rule);
            if ($payment) {
                return $payment;
            }
        }

        return null;
    }

    private function matchRecaudacion(MovimientoBancario $movimiento, ReglaConciliacion $rule): ?Recaudacion
    {
        if ($rule->usar_referencia) {
            return null;
        }

        $query = Recaudacion::query()
            ->where('estado_conciliacion', 'pendiente')
            ->where('cuenta_recaudadora_id', $movimiento->cuenta_recaudadora_id);

        $this->applyMontoRule($query, $movimiento, $rule, 'monto');
        $this->applyFechaRule($query, $movimiento, $rule, 'fecha');

        return $query->orderBy('fecha')->first();
    }

    private function matchPayment(MovimientoBancario $movimiento, ReglaConciliacion $rule): ?Payment
    {
        $query = Payment::query()->where('estado_conciliacion', 'pendiente');
        $creatorId = $movimiento->cuentaRecaudadora?->created_by;
        if ($creatorId) {
            $query->where('created_by', $creatorId);
        }

        if ($rule->usar_referencia && $movimiento->referencia) {
            $query->where('reference', $movimiento->referencia);
        } elseif ($rule->usar_referencia) {
            return null;
        }

        $this->applyMontoRule($query, $movimiento, $rule, 'amount');
        $this->applyFechaRule($query, $movimiento, $rule, 'date');

        return $query->orderBy('date')->first();
    }

    private function applyMontoRule($query, MovimientoBancario $movimiento, ReglaConciliacion $rule, string $column): void
    {
        if (!$rule->usar_monto) {
            return;
        }

        $tolerancia = (float) $rule->tolerancia_monto;
        $min = $movimiento->monto - $tolerancia;
        $max = $movimiento->monto + $tolerancia;
        $query->whereBetween($column, [$min, $max]);
    }

    private function applyFechaRule($query, MovimientoBancario $movimiento, ReglaConciliacion $rule, string $column): void
    {
        if (!$rule->usar_fecha) {
            return;
        }

        $rango = (int) $rule->rango_dias;
        $fecha = Carbon::parse($movimiento->fecha);
        $query->whereBetween($column, [
            $fecha->copy()->subDays($rango)->format('Y-m-d'),
            $fecha->copy()->addDays($rango)->format('Y-m-d'),
        ]);
    }

    private function getRegistroMonto(Model $registro): float
    {
        if ($registro instanceof Payment) {
            return (float) $registro->amount;
        }

        if ($registro instanceof Recaudacion) {
            return (float) $registro->monto;
        }

        return 0;
    }
}
