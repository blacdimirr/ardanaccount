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
        $itbisBilled = 0;
        $itbisWithheld = 0;
        $isrWithheld = 0;

        foreach ($items as $item) {
            $quantity = (float)($item['quantity'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $discount = (float)($item['discount'] ?? 0);
            $taxAmount = (float)($item['itemTaxPrice'] ?? 0);
            $categoryId = $item['category_id'] ?? null;

            if (!$categoryId) {
                $productId = $item['items'] ?? $item['item'] ?? null;

                if ($productId) {
                    $categoryId = ProductService::where('id', $productId)->value('category_id');
                }
            }

            $base = max(0, ($quantity * $price) - $discount);
            $itbisBilled += $taxAmount;

            $rule = $this->resolveRule($rules, $supplierType, $categoryId);
            $itbisWithheld += round($taxAmount * ($rule?->itbis_retention_rate ?? 0) / 100, 2);
            $isrWithheld += round($base * ($rule?->isr_retention_rate ?? 0) / 100, 2);
        }

        return [
            'itbis_billed_total' => round($itbisBilled, 2),
            'itbis_withheld_total' => round($itbisWithheld, 2),
            'isr_withheld_total' => round($isrWithheld, 2),
        ];
    }

    protected function resolveRule(Collection $rules, ?string $supplierType, $categoryId): ?RetentionRule
    {
        $candidates = $rules->filter(function (RetentionRule $rule) use ($supplierType, $categoryId) {
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
