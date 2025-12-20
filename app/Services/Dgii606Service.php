<?php

namespace App\Services;

use App\Models\Bill;
use Illuminate\Support\Collection;

class Dgii606Service
{
    public function getPurchasesForPeriod(int $year, int $month, int $creatorId): Collection
    {
        return Bill::with('vender', 'category')
            ->whereYear('bill_date', $year)
            ->whereMonth('bill_date', $month)
            ->where('created_by', $creatorId)
            ->get();
    }
}
