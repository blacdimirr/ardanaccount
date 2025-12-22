<?php

namespace App\Services;

use App\Models\RetentionRule;
use App\Models\ProductService;
use Illuminate\Support\Collection;

class RetentionCalculator
{
    /**
     * @param array<array<string,mixed>> $items
     */
    public function calculateForBill(array $items, ?string $supplierType, Collection $rules): array
    {
        $details = $this->calculateDetailedForBill($items, $supplierType, $rules);

        return $details['totals'];
    }

    public function calculateDetailedForBill(array $items, ?string $supplierType, Collection $rules): array
    {
        $itbisBilled = 0;
        $itbisWithheld = 0;
        $isrWithheld = 0;
        $governmentWithheld = 0;
        $lines = [];

        foreach ($items as $index => $item) {
            $line = $this->calculateLineRetention($item, $supplierType, $rules);
            $lines[$index] = $line;

            $itbisBilled += $line['itbis_billed'];
            $itbisWithheld += $line['itbis_withheld'];
            $isrWithheld += $line['isr_withheld'];
            $governmentWithheld += $line['government_withheld'];
        }

        return [
            'totals' => [
                'itbis_billed_total' => round($itbisBilled, 2),
                'itbis_withheld_total' => round($itbisWithheld, 2),
                'isr_withheld_total' => round($isrWithheld, 2),
                'government_withheld_total' => round($governmentWithheld, 2),
            ],
            'lines' => $lines,
        ];
    }

    public function calculateLineRetention(array $item, ?string $supplierType, Collection $rules): array
    {
        $quantity = (float)($item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        $discount = (float)($item['discount'] ?? 0);
        $taxAmount = (float)($item['itemTaxPrice'] ?? 0);
        $categoryId = $item['category_id'] ?? null;

        if (!$categoryId && isset($item['item'])) {
            $categoryId = ProductService::where('id', $item['item'])->value('category_id');
        }

        $base = max(0, ($quantity * $price) - $discount);
        $rule = $this->resolveRule($rules, $supplierType, $categoryId);

        if ($taxAmount <= 0 && isset($item['itemTaxRate'])) {
            $taxAmount = $base * ((float) $item['itemTaxRate'] / 100);
        }

        $itbisWithheld = round($taxAmount * ($rule?->itbis_retention_rate ?? 0) / 100, 2);
        $isrWithheld = round($base * ($rule?->isr_retention_rate ?? 0) / 100, 2);
        $governmentWithheld = round($base * ($rule?->government_retention_rate ?? 0) / 100, 2);

        return [
            'base' => $base,
            'itbis_billed' => round($taxAmount, 2),
            'itbis_withheld' => $itbisWithheld,
            'isr_withheld' => $isrWithheld,
            'government_withheld' => $governmentWithheld,
            'rule_id' => $rule?->id,
        ];
    }

    protected function resolveRule(Collection $rules, ?string $supplierType, $categoryId): ?RetentionRule
    {
        $activeRules = $rules->filter(function (RetentionRule $rule) {
            return $rule->active ?? true;
        });

        if (is_numeric($supplierType)) {
            $matched = $activeRules->firstWhere('id', (int) $supplierType);
            if ($matched) {
                return $matched;
            }
        }

        $candidates = $activeRules->filter(function (RetentionRule $rule) use ($supplierType, $categoryId) {
            $categoryMatches = $rule->service_category_id === null || (string)$rule->service_category_id === (string)$categoryId;
            $supplierMatches = $rule->supplier_type === null || $rule->supplier_type === $supplierType;

            return $categoryMatches && $supplierMatches;
        })->sortByDesc(function (RetentionRule $rule) use ($supplierType, $categoryId) {
            $score = 0;
            if ($rule->service_category_id !== null && $categoryId !== null) {
                $score += 2;
            }
            if ($rule->supplier_type !== null && $supplierType !== null) {
                $score += 1;
            }

            return $score;
        });

        return $candidates->first();
    }
}
