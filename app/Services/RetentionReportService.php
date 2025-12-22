<?php

namespace App\Services;

use App\Models\Bill;

class RetentionReportService
{
    public function summarizeBill(Bill $bill): array
    {
        $bill->loadMissing(['items', 'vender']);

        $lines = $bill->items->map(function ($item) {
            $base = max(0, ($item->quantity * $item->price) - ($item->discount ?? 0));

            return [
                'product_id' => $item->product_id,
                'description' => $item->description,
                'category_id' => $item->category_id,
                'base' => round($base, 2),
                'itbis_billed' => (float) ($item->itbis_amount ?? 0),
                'itbis_withheld' => (float) ($item->itbis_withheld_amount ?? 0),
                'isr_withheld' => (float) ($item->isr_withheld_amount ?? 0),
                'government_withheld' => (float) ($item->government_withheld_amount ?? 0),
                'retention_rule_id' => $item->retention_rule_id,
            ];
        })->values();

        return [
            'bill_id' => $bill->id,
            'bill_number' => $bill->bill_id,
            'vender_id' => $bill->vender_id,
            'supplier_type' => $bill->supplier_type,
            'totals' => [
                'itbis_billed_total' => (float) ($bill->itbis_billed_total ?? 0),
                'itbis_withheld_total' => (float) ($bill->itbis_withheld_total ?? 0),
                'isr_withheld_total' => (float) ($bill->isr_withheld_total ?? 0),
                'government_withheld_total' => (float) ($bill->government_withheld_total ?? 0),
                'net_payable' => $bill->getNetPayable(),
            ],
            'lines' => $lines,
        ];
    }
}
