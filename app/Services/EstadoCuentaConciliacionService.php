<?php

namespace App\Services;

use App\Models\MovimientoBancario;
use App\Models\Payment;
use App\Models\Recaudacion;
use Illuminate\Support\Collection;

class EstadoCuentaConciliacionService
{
    public function buildStatement(?int $accountId, array $allowedAccountIds, string $startDate, string $endDate): array
    {
        $movimientosQuery = MovimientoBancario::with(['cuentaRecaudadora', 'conciliable'])
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate);

        $openingBalanceQuery = MovimientoBancario::query()->whereDate('fecha', '<', $startDate);

        if (!empty($accountId)) {
            $movimientosQuery->where('cuenta_recaudadora_id', $accountId);
            $openingBalanceQuery->where('cuenta_recaudadora_id', $accountId);
        } elseif (!empty($allowedAccountIds)) {
            $movimientosQuery->whereIn('cuenta_recaudadora_id', $allowedAccountIds);
            $openingBalanceQuery->whereIn('cuenta_recaudadora_id', $allowedAccountIds);
        }

        $movimientos = $movimientosQuery->orderBy('fecha')->orderBy('id')->get();
        $openingBalance = (float) $openingBalanceQuery->sum('monto');
        $runningBalance = $openingBalance;

        $rows = $movimientos->map(function (MovimientoBancario $movimiento) use (&$runningBalance) {
            $runningBalance += (float) $movimiento->monto;

            return [
                'movimiento' => $movimiento,
                'matched_label' => $this->matchedLabel($movimiento),
                'matched_amount' => $this->matchedAmount($movimiento),
                'saldo' => $runningBalance,
            ];
        });

        $periodTotal = (float) $movimientos->sum('monto');

        return [
            'opening_balance' => $openingBalance,
            'period_total' => $periodTotal,
            'closing_balance' => $openingBalance + $periodTotal,
            'rows' => $rows,
            'movimientos' => $movimientos,
        ];
    }

    public function buildDifferences(?int $accountId, array $allowedAccountIds, string $startDate, string $endDate): Collection
    {
        $movimientosQuery = MovimientoBancario::with(['cuentaRecaudadora', 'conciliable'])
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate)
            ->orderBy('fecha')
            ->orderBy('id');

        if (!empty($accountId)) {
            $movimientosQuery->where('cuenta_recaudadora_id', $accountId);
        } elseif (!empty($allowedAccountIds)) {
            $movimientosQuery->whereIn('cuenta_recaudadora_id', $allowedAccountIds);
        }

        $movimientos = $movimientosQuery->get();

        return $movimientos->map(function (MovimientoBancario $movimiento) {
            $matchedAmount = $this->matchedAmount($movimiento);
            $difference = $matchedAmount !== null
                ? (float) $movimiento->monto - $matchedAmount
                : (float) $movimiento->monto;

            return [
                'movimiento' => $movimiento,
                'matched_label' => $this->matchedLabel($movimiento),
                'matched_amount' => $matchedAmount,
                'difference' => $difference,
            ];
        })->filter(function (array $row) {
            return $row['movimiento']->estado_conciliacion !== 'conciliado' || abs($row['difference']) > 0.01;
        })->values();
    }

    private function matchedAmount(MovimientoBancario $movimiento): ?float
    {
        if (!$movimiento->conciliable) {
            return null;
        }

        if ($movimiento->conciliable_type === Recaudacion::class) {
            return (float) $movimiento->conciliable->monto;
        }

        if ($movimiento->conciliable_type === Payment::class) {
            return (float) $movimiento->conciliable->amount;
        }

        return null;
    }

    private function matchedLabel(MovimientoBancario $movimiento): string
    {
        if (!$movimiento->conciliable) {
            return '-';
        }

        if ($movimiento->conciliable_type === Recaudacion::class) {
            return __('Collection') . ' #' . $movimiento->conciliable->id;
        }

        if ($movimiento->conciliable_type === Payment::class) {
            return __('Supplier Payment') . ' #' . $movimiento->conciliable->id;
        }

        return '-';
    }
}
