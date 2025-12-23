<?php

namespace App\Services;

use App\Models\BillProduct;
use App\Models\Budget;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BudgetExecutionService
{
    public function findBudgetForDate(int $creatorId, $date): ?Budget
    {
        $year = Carbon::parse($date ?? now())->format('Y');

        return Budget::where('created_by', $creatorId)
            ->where('from', $year)
            ->latest('id')
            ->first();
    }

    public function ensureCommitmentAvailability(?Budget $budget, array $categoryAmounts): void
    {
        if (!$budget || empty($categoryAmounts)) {
            return;
        }

        $pimTotals = $this->normalizeTotals($budget->monto_pim);
        $committedTotals = $this->normalizeTotals($budget->monto_comprometido, $pimTotals);

        $messages = [];
        foreach ($categoryAmounts as $categoryId => $amount) {
            $currentCommitted = data_get($committedTotals, 'expense.' . $categoryId, 0);
            $pim = data_get($pimTotals, 'expense.' . $categoryId, 0);

            if ($currentCommitted + $amount > $pim) {
                $categoryName = ProductServiceCategory::find($categoryId)?->name ?? __('Unknown category');
                $messages[] = __('Committed amount exceeds PIM for :category.', ['category' => $categoryName]);
            }
        }

        if (!empty($messages)) {
            throw new \RuntimeException(implode(' ', $messages));
        }
    }

    public function applyCommitment(?Budget $budget, array $categoryAmounts): void
    {
        $this->incrementPhase($budget, 'monto_comprometido', $categoryAmounts);
    }

    public function applyCommitmentDelta(?Budget $budget, array $delta): void
    {
        $this->incrementPhase($budget, 'monto_comprometido', $delta);
    }

    public function applyAccrualDelta(?Budget $budget, array $delta): void
    {
        $this->incrementPhase($budget, 'monto_devengado', $delta);
    }

    public function applyPaymentDelta(?Budget $budget, array $delta): void
    {
        $this->incrementPhase($budget, 'monto_pagado', $delta);
    }

    public function summarizeLinesForCommitment(array $lines, array $retentionLines = [], array $productCategoryMap = []): array
    {
        $totals = [];
        foreach ($lines as $index => $line) {
            $categoryId = $line['category_id'] ?? null;
            $productId = $line['item'] ?? $line['items'] ?? null;

            if (!$categoryId && $productId && isset($productCategoryMap[$productId])) {
                $categoryId = $productCategoryMap[$productId];
            }

            if (!$categoryId) {
                continue;
            }

            $totals[$categoryId] = ($totals[$categoryId] ?? 0) + $this->calculateLineTotal(
                (float) ($line['quantity'] ?? 0),
                (float) ($line['price'] ?? 0),
                (float) ($line['discount'] ?? 0),
                (float) (data_get($retentionLines, $index . '.itbis_billed') ?? ($line['itbis_amount'] ?? $line['itemTaxPrice'] ?? 0))
            );
        }

        return $totals;
    }

    public function summarizeExistingBillProducts(Collection $items, array $productCategoryMap = []): array
    {
        $totals = [];
        foreach ($items as $item) {
            $categoryId = $item->category_id ?? ($productCategoryMap[$item->product_id] ?? null);

            if (!$categoryId) {
                continue;
            }

            $totals[$categoryId] = ($totals[$categoryId] ?? 0) + $this->calculateLineTotal(
                (float) ($item->quantity ?? 0),
                (float) ($item->price ?? 0),
                (float) ($item->discount ?? 0),
                (float) ($item->itbis_amount ?? 0)
            );
        }

        return $totals;
    }

    public function calculateAccrualDelta(BillProduct $line, float $newReceivedQuantity, array $productCategoryMap = []): array
    {
        $categoryId = $line->category_id ?? ($productCategoryMap[$line->product_id] ?? null);

        if (!$categoryId) {
            return [];
        }

        $baseQuantity = max(0.0, (float) $line->quantity);
        $previousAmount = $this->calculatePartialAmount(
            $baseQuantity,
            (float) ($line->received_quantity ?? 0),
            (float) ($line->price ?? 0),
            (float) ($line->discount ?? 0),
            (float) ($line->itbis_amount ?? 0)
        );
        $newAmount = $this->calculatePartialAmount(
            $baseQuantity,
            min($newReceivedQuantity, $baseQuantity),
            (float) ($line->price ?? 0),
            (float) ($line->discount ?? 0),
            (float) ($line->itbis_amount ?? 0)
        );

        return [$categoryId => $newAmount - $previousAmount];
    }

    public function paymentImpact(Payment $payment): array
    {
        $category = ProductServiceCategory::find($payment->category_id);

        if (!$category || $category->type !== 'expense') {
            return [];
        }

        return [$category->id => (float) $payment->amount];
    }

    public function delta(array $previous, array $current): array
    {
        $allKeys = array_unique(array_merge(array_keys($previous), array_keys($current)));
        $delta = [];

        foreach ($allKeys as $key) {
            $delta[$key] = ($current[$key] ?? 0) - ($previous[$key] ?? 0);
        }

        return array_filter($delta, function ($value) {
            return abs($value) > 0;
        });
    }

    protected function incrementPhase(?Budget $budget, string $field, array $delta): void
    {
        if (!$budget || empty($delta)) {
            return;
        }

        $pimTotals = $this->normalizeTotals($budget->monto_pim);
        $currentTotals = $this->normalizeTotals($budget->{$field}, $pimTotals);

        foreach ($delta as $categoryId => $change) {
            $existing = data_get($currentTotals, 'expense.' . $categoryId, 0);
            $currentTotals['expense'][$categoryId] = max(0, $existing + $change);
        }

        $budget->{$field} = $currentTotals;
        $budget->save();
    }

    protected function normalizeTotals($value, array $pimTotals = []): array
    {
        $normalized = ['income' => [], 'expense' => []];

        if (is_array($value)) {
            $normalized = array_merge($normalized, $value);
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $normalized = array_merge($normalized, $decoded);
            }
        }

        foreach (['income', 'expense'] as $type) {
            $pimByType = $pimTotals[$type] ?? [];
            foreach ($pimByType as $categoryId => $amount) {
                if (!array_key_exists($categoryId, $normalized[$type])) {
                    $normalized[$type][$categoryId] = 0;
                }
            }
        }

        return $normalized;
    }

    protected function calculateLineTotal(float $quantity, float $price, float $discount, float $taxAmount): float
    {
        $base = max(0, ($quantity * $price) - $discount);

        return $base + $taxAmount;
    }

    protected function calculatePartialAmount(float $baseQuantity, float $targetQuantity, float $price, float $discount, float $taxAmount): float
    {
        if ($baseQuantity <= 0 || $targetQuantity <= 0) {
            return 0.0;
        }

        $perUnit = $this->calculateLineTotal($baseQuantity, $price, $discount, $taxAmount) / $baseQuantity;

        return $perUnit * min($targetQuantity, $baseQuantity);
    }
}
