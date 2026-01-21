<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\TransactionLines;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HistoricoContableJournalService
{
    public function generateForBatch(int $batchId, array $options = []): array
    {
        $createdBy = (int) ($options['created_by'] ?? 1);
        $rules = $this->loadRules($createdBy);

        $summary = [
            'payments' => 0,
            'revenues' => 0,
            'bills' => 0,
            'skipped' => 0,
        ];

        DB::transaction(function () use ($batchId, $createdBy, $rules, &$summary) {
            $summary['payments'] = $this->generateFromPayments($batchId, $createdBy, $rules);
            $summary['revenues'] = $this->generateFromRevenues($batchId, $createdBy, $rules);
            $summary['bills'] = $this->generateFromBills($batchId, $createdBy, $rules);
        });

        return $summary;
    }

    public function rollbackBatch(int $batchId): void
    {
        DB::transaction(function () use ($batchId) {
            TransactionLines::where('migration_batch_id', $batchId)->delete();
            JournalItem::where('migration_batch_id', $batchId)->delete();
            JournalEntry::where('migration_batch_id', $batchId)->delete();
        });
    }

    private function generateFromPayments(int $batchId, int $createdBy, Collection $rules): int
    {
        $count = 0;

        Payment::where('migration_batch_id', $batchId)
            ->orderBy('id')
            ->chunkById(200, function ($payments) use (&$count, $createdBy, $rules, $batchId) {
                foreach ($payments as $payment) {
                    if ($this->journalExists('payment', $payment->id, $batchId)) {
                        continue;
                    }

                    $amount = (float) $payment->amount;
                    if ($amount <= 0) {
                        continue;
                    }

                    $bankAccountId = $this->resolveBankChartAccountId($payment->account_id, $createdBy);
                    $debitAccountId = $this->resolveRuleAccount(
                        $rules,
                        'payment',
                        $payment->description ?? '',
                        'debit',
                        config('historico_contable.accounting_defaults.expense_account_code'),
                        $createdBy
                    );

                    if (!$bankAccountId || !$debitAccountId) {
                        continue;
                    }

                    $entry = $this->createJournalEntry([
                        'date' => $payment->date,
                        'reference' => 'HISTORICO-PAGO-' . $payment->id,
                        'description' => $payment->description ?? 'Pago histórico',
                        'created_by' => $createdBy,
                        'migration_batch_id' => $batchId,
                        'source_type' => 'payment',
                        'source_id' => $payment->id,
                        'source_hash' => $payment->source_hash,
                    ]);

                    $debitItem = $this->createJournalItem($entry, $debitAccountId, $amount, 0, $batchId, 'payment', $payment->id, $payment->source_hash, 'Gasto pago histórico');
                    $creditItem = $this->createJournalItem($entry, $bankAccountId, 0, $amount, $batchId, 'payment', $payment->id, $payment->source_hash, 'Salida banco');

                    $this->createTransactionLine($debitAccountId, $debitItem, $entry, $amount, 'Debit', $createdBy, $batchId, 'payment', $payment->id, $payment->source_hash);
                    $this->createTransactionLine($bankAccountId, $creditItem, $entry, $amount, 'Credit', $createdBy, $batchId, 'payment', $payment->id, $payment->source_hash);

                    $count++;
                }
            });

        return $count;
    }

    private function generateFromRevenues(int $batchId, int $createdBy, Collection $rules): int
    {
        $count = 0;

        Revenue::where('migration_batch_id', $batchId)
            ->orderBy('id')
            ->chunkById(200, function ($revenues) use (&$count, $createdBy, $rules, $batchId) {
                foreach ($revenues as $revenue) {
                    if ($this->journalExists('revenue', $revenue->id, $batchId)) {
                        continue;
                    }

                    $amount = (float) $revenue->amount;
                    if ($amount <= 0) {
                        continue;
                    }

                    $bankAccountId = $this->resolveBankChartAccountId($revenue->account_id, $createdBy);
                    $creditAccountId = $this->resolveRuleAccount(
                        $rules,
                        'income',
                        $revenue->description ?? '',
                        'credit',
                        config('historico_contable.accounting_defaults.income_other_account_code'),
                        $createdBy
                    );

                    if (!$bankAccountId || !$creditAccountId) {
                        continue;
                    }

                    $entry = $this->createJournalEntry([
                        'date' => $revenue->date,
                        'reference' => 'HISTORICO-ING-' . $revenue->id,
                        'description' => $revenue->description ?? 'Ingreso histórico',
                        'created_by' => $createdBy,
                        'migration_batch_id' => $batchId,
                        'source_type' => 'revenue',
                        'source_id' => $revenue->id,
                        'source_hash' => $revenue->source_hash,
                    ]);

                    $debitItem = $this->createJournalItem($entry, $bankAccountId, $amount, 0, $batchId, 'revenue', $revenue->id, $revenue->source_hash, 'Ingreso banco');
                    $creditItem = $this->createJournalItem($entry, $creditAccountId, 0, $amount, $batchId, 'revenue', $revenue->id, $revenue->source_hash, 'Ingreso por origen');

                    $this->createTransactionLine($bankAccountId, $debitItem, $entry, $amount, 'Debit', $createdBy, $batchId, 'revenue', $revenue->id, $revenue->source_hash);
                    $this->createTransactionLine($creditAccountId, $creditItem, $entry, $amount, 'Credit', $createdBy, $batchId, 'revenue', $revenue->id, $revenue->source_hash);

                    $count++;
                }
            });

        return $count;
    }

    private function generateFromBills(int $batchId, int $createdBy, Collection $rules): int
    {
        $count = 0;

        Bill::where('migration_batch_id', $batchId)
            ->orderBy('id')
            ->chunkById(200, function ($bills) use (&$count, $createdBy, $rules, $batchId) {
                foreach ($bills as $bill) {
                    if ($this->journalExists('bill', $bill->id, $batchId)) {
                        continue;
                    }

                    $amount = $this->resolveBillAmount($bill);
                    if ($amount <= 0) {
                        continue;
                    }

                    $detalle = $this->resolveBillDescription($bill);
                    $debitAccountId = $this->resolveRuleAccount(
                        $rules,
                        'bill',
                        $detalle,
                        'debit',
                        config('historico_contable.accounting_defaults.expense_account_code'),
                        $createdBy
                    );
                    $creditAccountId = $this->resolveAccountByCode(
                        config('historico_contable.accounting_defaults.payables_account_code'),
                        $createdBy
                    );

                    if (!$debitAccountId || !$creditAccountId) {
                        continue;
                    }

                    $entry = $this->createJournalEntry([
                        'date' => $bill->bill_date,
                        'reference' => 'HISTORICO-OC-' . $bill->id,
                        'description' => $detalle ?: 'Orden de compra histórica',
                        'created_by' => $createdBy,
                        'migration_batch_id' => $batchId,
                        'source_type' => 'bill',
                        'source_id' => $bill->id,
                        'source_hash' => $bill->source_hash,
                    ]);

                    $debitItem = $this->createJournalItem($entry, $debitAccountId, $amount, 0, $batchId, 'bill', $bill->id, $bill->source_hash, 'Gasto OC');
                    $creditItem = $this->createJournalItem($entry, $creditAccountId, 0, $amount, $batchId, 'bill', $bill->id, $bill->source_hash, 'CxP proveedores');

                    $this->createTransactionLine($debitAccountId, $debitItem, $entry, $amount, 'Debit', $createdBy, $batchId, 'bill', $bill->id, $bill->source_hash);
                    $this->createTransactionLine($creditAccountId, $creditItem, $entry, $amount, 'Credit', $createdBy, $batchId, 'bill', $bill->id, $bill->source_hash);

                    $count++;
                }
            });

        return $count;
    }

    private function loadRules(int $createdBy): Collection
    {
        $dbRules = DB::table('historico_accounting_rules')
            ->where('is_active', true)
            ->whereIn('created_by', [0, $createdBy])
            ->get()
            ->map(fn ($rule) => (array) $rule);

        $configRules = collect(config('historico_contable.accounting_rules', []));

        return $configRules->merge($dbRules);
    }

    private function resolveRuleAccount(Collection $rules, string $documentType, string $value, string $side, string $defaultCode, int $createdBy): ?int
    {
        $value = mb_strtoupper($value ?? '', 'UTF-8');
        foreach ($rules as $rule) {
            if (($rule['document_type'] ?? null) !== $documentType) {
                continue;
            }
            if (!$this->matchRule($rule, $value)) {
                continue;
            }
            $code = $side === 'debit' ? ($rule['debit_account_code'] ?? null) : ($rule['credit_account_code'] ?? null);
            if ($code) {
                return $this->resolveAccountByCode($code, $createdBy);
            }
        }

        return $this->resolveAccountByCode($defaultCode, $createdBy);
    }

    private function matchRule(array $rule, string $value): bool
    {
        $matchValue = mb_strtoupper((string) ($rule['match_value'] ?? ''), 'UTF-8');
        $matchType = $rule['match_type'] ?? 'contains';

        if ($matchType === 'exact') {
            return trim($value) === trim($matchValue);
        }
        if ($matchType === 'regex') {
            return (bool) preg_match($matchValue, $value);
        }

        return str_contains($value, $matchValue);
    }

    private function resolveAccountByCode(?string $code, int $createdBy): ?int
    {
        if (!$code) {
            return null;
        }

        $account = ChartOfAccount::where('code', $code)
            ->whereIn('created_by', [$createdBy, 1, 0])
            ->orderBy('created_by')
            ->first();

        if (!$account) {
            return null;
        }

        if ((int) $account->created_by === $createdBy) {
            return $account->id;
        }

        $tenantAccount = ChartOfAccount::firstOrCreate(
            [
                'created_by' => $createdBy,
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

    private function resolveBankChartAccountId(?int $bankAccountId, int $createdBy): ?int
    {
        if ($bankAccountId) {
            $bankAccount = BankAccount::find($bankAccountId);
            if ($bankAccount && $bankAccount->chart_account_id) {
                return $bankAccount->chart_account_id;
            }
        }

        return $this->resolveAccountByCode(config('historico_contable.accounting_defaults.bank_account_code'), $createdBy);
    }

    private function resolveBillAmount(Bill $bill): float
    {
        $items = BillProduct::where('bill_id', $bill->id)->get();
        if ($items->isEmpty()) {
            return 0.0;
        }

        return (float) $items->sum(function ($item) {
            return ((float) $item->price) * ((float) $item->quantity);
        });
    }

    private function resolveBillDescription(Bill $bill): string
    {
        $item = BillProduct::where('bill_id', $bill->id)->orderBy('id')->first();
        return $item?->description ?? '';
    }

    private function journalExists(string $sourceType, int $sourceId, int $batchId): bool
    {
        return JournalEntry::where('migration_batch_id', $batchId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->exists();
    }

    private function createJournalEntry(array $data): JournalEntry
    {
        $data['journal_id'] = $this->nextJournalNumber($data['created_by']);

        return JournalEntry::create($data);
    }

    private function createJournalItem(JournalEntry $entry, int $accountId, float $debit, float $credit, int $batchId, string $sourceType, int $sourceId, ?string $sourceHash, string $description): JournalItem
    {
        return JournalItem::create([
            'journal' => $entry->id,
            'account' => $accountId,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'migration_batch_id' => $batchId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_hash' => $sourceHash,
        ]);
    }

    private function createTransactionLine(int $accountId, JournalItem $item, JournalEntry $entry, float $amount, string $type, int $createdBy, int $batchId, string $sourceType, int $sourceId, ?string $sourceHash): void
    {
        TransactionLines::create([
            'account_id' => $accountId,
            'reference' => 'Journal',
            'reference_id' => $entry->id,
            'reference_sub_id' => $item->id,
            'date' => $entry->date,
            'credit' => $type === 'Credit' ? $amount : 0,
            'debit' => $type === 'Debit' ? $amount : 0,
            'created_by' => $createdBy,
            'migration_batch_id' => $batchId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_hash' => $sourceHash,
        ]);

        $this->updateBankBalances($accountId, $type === 'Debit' ? $amount : 0, $type === 'Credit' ? $amount : 0);
    }

    private function nextJournalNumber(int $creatorId): int
    {
        $latest = JournalEntry::where('created_by', $creatorId)->latest()->first();

        return $latest ? $latest->journal_id + 1 : 1;
    }

    private function updateBankBalances(int $accountId, float $debit, float $credit): void
    {
        $bankAccounts = BankAccount::where('chart_account_id', $accountId)->get();
        if ($bankAccounts->isEmpty()) {
            return;
        }

        foreach ($bankAccounts as $bankAccount) {
            $oldBalance = (float) ($bankAccount->opening_balance ?? 0);
            $newBalance = $oldBalance + $debit - $credit;
            $bankAccount->opening_balance = $newBalance;
            $bankAccount->save();
        }
    }
}
