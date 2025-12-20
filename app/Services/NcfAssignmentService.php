<?php

namespace App\Services;

use App\Exceptions\NcfException;
use App\Models\NcfSeries;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NcfAssignmentService
{
    /**
    * Assign the next available NCF number for the given series.
    *
    * @param int|null $seriesId
    * @param int|null $expectedTypeId
    *
    * @throws NcfException
    */
    public function assignNextNumber(?int $seriesId, ?int $expectedTypeId = null): ?array
    {
        if (!$seriesId) {
            return null;
        }

        $result = DB::transaction(function () use ($seriesId, $expectedTypeId) {
            $series = NcfSeries::where('id', $seriesId)->lockForUpdate()->first();

            if (!$series) {
                return ['error' => __('La serie de NCF seleccionada no existe.')];
            }

            if ($expectedTypeId && $series->ncf_type_id !== $expectedTypeId) {
                return ['error' => __('El tipo de NCF no coincide con la serie seleccionada.')];
            }

            $today = Carbon::today();
            $validFrom = $series->valid_from ? Carbon::parse($series->valid_from) : null;
            $validTo = $series->valid_to ? Carbon::parse($series->valid_to) : null;

            if ($validFrom && $today->lt($validFrom)) {
                return ['error' => __('La serie de NCF aún no está vigente.')];
            }

            if ($validTo && $today->gt($validTo)) {
                if ($series->status !== 'vencido') {
                    $series->status = 'vencido';
                    $series->save();
                }

                return ['error' => __('El rango de NCF está vencido.')];
            }

            $current = $series->current_number ?? ($series->start_number - 1);
            $next = $current + 1;

            if ($next > $series->end_number) {
                if ($series->status !== 'agotado') {
                    $series->status = 'agotado';
                    $series->save();
                }

                return ['error' => __('El rango de NCF está agotado.')];
            }

            $series->current_number = $next;
            if ($next === $series->end_number) {
                $series->status = 'agotado';
            } elseif ($series->status === 'vencido') {
                $series->status = 'activo';
            }
            $series->save();

            return [
                'data' => [
                    'type_id' => $series->ncf_type_id,
                    'series_id' => $series->id,
                    'number' => (string) $next,
                ],
            ];
        });

        if (isset($result['error'])) {
            throw new NcfException($result['error']);
        }

        return $result['data'] ?? null;
    }
}
