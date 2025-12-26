<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\NominaDetalle;
use App\Models\NominaPeriodo;
use App\Models\Utility;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NominaAsientoService
{
    public function previewAsientoPorServicio(NominaPeriodo $periodo, int $creatorId): array
    {
        $detalles = $this->detallesConServicios($periodo, $creatorId);

        $resumen = [];

        foreach ($detalles as $detalle) {
            $concepto = $detalle->concepto;
            if (!$concepto) {
                continue;
            }

            $servicioId = $detalle->servicioUnidad?->id
                ?? $detalle->empleado?->servicioUnidad?->id;
            $servicioNombre = $detalle->servicioUnidad?->nombre
                ?? $detalle->empleado?->servicioUnidad?->nombre
                ?? $detalle->empleado?->unidad_servicio
                ?? __('Sin asignar');

            $key = $servicioId ? 'id:' . $servicioId : 'nombre:' . Str::lower($servicioNombre);

            if (!isset($resumen[$key])) {
                $resumen[$key] = [
                    'servicio_id' => $servicioId,
                    'servicio' => $servicioNombre,
                    'gastos' => 0,
                    'descuentos' => 0,
                ];
            }

            if ($concepto->tipo === 'ingreso') {
                $resumen[$key]['gastos'] += (float) $detalle->monto;
            } elseif ($concepto->tipo === 'descuento') {
                $resumen[$key]['descuentos'] += (float) $detalle->monto;
            }
        }

        $servicios = collect($resumen)->values()->map(function ($item) {
            $item['neto'] = $item['gastos'] - $item['descuentos'];

            return $item;
        })->sortBy('servicio')->values();

        $totales = [
            'gastos' => $servicios->sum('gastos'),
            'descuentos' => $servicios->sum('descuentos'),
            'neto' => $servicios->sum('neto'),
        ];

        return [
            'services' => $servicios->all(),
            'totales' => $totales,
        ];
    }

    public function generarAsientoPorServicio(NominaPeriodo $periodo, int $creatorId): JournalEntry
    {
        if ($periodo->journal_entry_id && $periodo->journalEntry) {
            return $periodo->journalEntry;
        }

        $resumen = $this->previewAsientoPorServicio($periodo, $creatorId);

        if (empty($resumen['services'])) {
            throw new \RuntimeException(__('No hay detalles de nómina para generar el asiento.'));
        }

        $expenseAccountId = $this->getExpenseAccountId($creatorId);
        if (!$expenseAccountId) {
            throw new \RuntimeException(__('No se encontró una cuenta de gastos de nómina.'));
        }

        $bankAccountId = $this->getBankAccountId($creatorId);
        if (!$bankAccountId) {
            throw new \RuntimeException(__('No se encontró una cuenta bancaria para registrar el pago de nómina.'));
        }

        $liabilityAccountId = $this->getLiabilityAccountId($creatorId);
        if ($resumen['totales']['descuentos'] > 0 && !$liabilityAccountId) {
            throw new \RuntimeException(__('No se encontró una cuenta de pasivos para retenciones de nómina.'));
        }

        $journalEntry = new JournalEntry();
        $journalEntry->journal_id = $this->nextJournalNumber($creatorId);
        $journalEntry->date = $periodo->fecha_fin;
        $journalEntry->reference = __('Nomina');
        $journalEntry->description = __('Asiento de nómina del periodo') . ' ' . $periodo->nombre;
        $journalEntry->created_by = $creatorId;
        $journalEntry->save();

        foreach ($resumen['services'] as $service) {
            if ($service['gastos'] <= 0) {
                continue;
            }

            $journalItem = new JournalItem();
            $journalItem->journal = $journalEntry->id;
            $journalItem->account = $expenseAccountId;
            $journalItem->servicio_id = $service['servicio_id'];
            $journalItem->description = __('Gasto nómina') . ' - ' . $service['servicio'];
            $journalItem->debit = $service['gastos'];
            $journalItem->credit = 0;
            $journalItem->save();

            Utility::addTransactionLines([
                'account_id' => $expenseAccountId,
                'transaction_type' => 'Debit',
                'transaction_amount' => $service['gastos'],
                'reference' => 'Nomina',
                'reference_id' => $journalEntry->id,
                'reference_sub_id' => $journalItem->id,
                'date' => $journalEntry->date,
            ]);
        }

        if ($resumen['totales']['descuentos'] > 0 && $liabilityAccountId) {
            $journalItem = new JournalItem();
            $journalItem->journal = $journalEntry->id;
            $journalItem->account = $liabilityAccountId;
            $journalItem->description = __('Pasivos por nómina');
            $journalItem->debit = 0;
            $journalItem->credit = $resumen['totales']['descuentos'];
            $journalItem->save();

            Utility::addTransactionLines([
                'account_id' => $liabilityAccountId,
                'transaction_type' => 'Credit',
                'transaction_amount' => $resumen['totales']['descuentos'],
                'reference' => 'Nomina',
                'reference_id' => $journalEntry->id,
                'reference_sub_id' => $journalItem->id,
                'date' => $journalEntry->date,
            ]);
        }

        if ($resumen['totales']['neto'] > 0) {
            $journalItem = new JournalItem();
            $journalItem->journal = $journalEntry->id;
            $journalItem->account = $bankAccountId;
            $journalItem->description = __('Pago de nómina');
            $journalItem->debit = 0;
            $journalItem->credit = $resumen['totales']['neto'];
            $journalItem->save();

            Utility::addTransactionLines([
                'account_id' => $bankAccountId,
                'transaction_type' => 'Credit',
                'transaction_amount' => $resumen['totales']['neto'],
                'reference' => 'Nomina',
                'reference_id' => $journalEntry->id,
                'reference_sub_id' => $journalItem->id,
                'date' => $journalEntry->date,
            ]);
        }

        $periodo->journal_entry_id = $journalEntry->id;
        $periodo->save();

        return $journalEntry;
    }

    private function detallesConServicios(NominaPeriodo $periodo, int $creatorId): Collection
    {
        return NominaDetalle::with(['concepto', 'empleado.servicioUnidad', 'servicioUnidad'])
            ->where('created_by', $creatorId)
            ->where('nomina_periodo_id', $periodo->id)
            ->get();
    }

    private function getExpenseAccountId(int $creatorId): ?int
    {
        $account = ChartOfAccount::where('created_by', $creatorId)
            ->where('name', 'Salaries and Wages')
            ->first();

        if ($account) {
            return $account->id;
        }

        $subType = ChartOfAccountSubType::where('created_by', $creatorId)
            ->where('name', 'Payroll Expenses')
            ->first();

        if ($subType) {
            return ChartOfAccount::where('created_by', $creatorId)
                ->where('sub_type', $subType->id)
                ->orderBy('code')
                ->value('id');
        }

        return null;
    }

    private function getLiabilityAccountId(int $creatorId): ?int
    {
        $createdByScope = [$creatorId, 1];

        $account = ChartOfAccount::whereIn('created_by', $createdByScope)
            ->where('name', 'Accr. Benefits - Payroll Taxes')
            ->first();

        if ($account) {
            return $this->resolveAccountForTenant($account, $creatorId);
        }

        $account = $this->ensureLiabilityTemplate($creatorId, $createdByScope);
        if ($account) {
            return $account;
        }

        $type = ChartOfAccountType::whereIn('created_by', $createdByScope)
            ->where('name', 'Liabilities')
            ->first();

        if (!$type) {
            return null;
        }

        $account = ChartOfAccount::whereIn('created_by', $createdByScope)
            ->where('type', $type->id)
            ->where('name', 'like', '%Payroll%')
            ->orderBy('code')
            ->first();

        return $this->resolveAccountForTenant($account, $creatorId);
    }

    private function getBankAccountId(int $creatorId): ?int
    {
        $bankAccount = BankAccount::where('created_by', $creatorId)
            ->whereNotNull('chart_account_id')
            ->orderBy('id')
            ->first();
        $createdByScope = [$creatorId, 1];

        if ($bankAccount?->chart_account_id) {
            return $bankAccount->chart_account_id;
        }

        $bankAccount = BankAccount::where('created_by', $creatorId)->orderBy('id')->first();
        if ($bankAccount) {
            $matchedAccount = ChartOfAccount::whereIn('created_by', $createdByScope)
                ->whereIn('name', array_filter([
                    $bankAccount->holder_name,
                    $bankAccount->bank_name,
                ]))
                ->orderBy('code')
                ->first();

            if ($matchedAccount) {
                $resolvedId = $this->resolveAccountForTenant($matchedAccount, $creatorId);

                if ($resolvedId) {
                    $bankAccount->chart_account_id = $resolvedId;
                    $bankAccount->save();

                    return $resolvedId;
                }

                return null;
            }
        }

        $account = ChartOfAccount::whereIn('created_by', $createdByScope)
            ->whereIn('name', ['Checking Account', 'Petty Cash'])
            ->orderBy('code')
            ->first();

        if ($account) {
            return $this->resolveAccountForTenant($account, $creatorId);
        }

        $assetType = ChartOfAccountType::whereIn('created_by', $createdByScope)
            ->where('name', 'Assets')
            ->first();

        if (!$assetType) {
            return null;
        }

        $account = ChartOfAccount::whereIn('created_by', $createdByScope)
            ->where('type', $assetType->id)
            ->where(function ($query) {
                $query->where('name', 'like', '%Cash%')
                    ->orWhere('name', 'like', '%Bank%');
            })
            ->orderBy('code')
            ->first();

        return $this->resolveAccountForTenant($account, $creatorId);
    }

    private function nextJournalNumber(int $creatorId): int
    {
        $latest = JournalEntry::where('created_by', $creatorId)->latest()->first();

        return $latest ? $latest->journal_id + 1 : 1;
    }

    private function resolveAccountForTenant(?ChartOfAccount $account, int $creatorId): ?int
    {
        if (!$account) {
            return null;
        }

        if ((int) $account->created_by === $creatorId) {
            return $account->id;
        }

        $tenantAccount = ChartOfAccount::firstOrCreate(
            [
                'created_by' => $creatorId,
                'name' => $account->name,
            ],
            [
                'code' => $account->code,
                'type' => $account->type,
                'sub_type' => $account->sub_type,
                'is_enabled' => $account->is_enabled ?? 1,
            ]
        );

        return $tenantAccount->id;
    }

    private function ensureLiabilityTemplate(int $creatorId, array $createdByScope): ?int
    {
        $template = ChartOfAccount::whereIn('created_by', $createdByScope)
            ->where('name', 'Accr. Benefits - Payroll Taxes')
            ->orderBy('created_by')
            ->first();

        if ($template) {
            return $this->resolveAccountForTenant($template, $creatorId);
        }

        $type = ChartOfAccountType::whereIn('created_by', $createdByScope)
            ->where('name', 'Liabilities')
            ->first();

        if (!$type) {
            return null;
        }

        $subType = \App\Models\ChartOfAccountSubType::whereIn('created_by', $createdByScope)
            ->where('type', $type->id)
            ->where('name', 'Current Liabilities')
            ->first();

        $account = ChartOfAccount::firstOrCreate(
            [
                'created_by' => $creatorId,
                'name' => 'Accr. Benefits - Payroll Taxes',
            ],
            [
                'code' => '2340',
                'type' => $type->id,
                'sub_type' => $subType?->id,
                'is_enabled' => 1,
            ]
        );

        return $account->id;
    }
}
