<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class Dgii607Service
{
    public function getSalesForPeriod(int $year, int $month, int $creatorId): Collection
    {
        return Invoice::with('customer')
            ->whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->where('created_by', $creatorId)
            ->get();
    }
}
