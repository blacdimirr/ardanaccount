<?php

namespace App\Services;

use App\Models\CreditNote;
use Illuminate\Support\Collection;

class Dgii608Service
{
    public function getAnulationsForPeriod(int $year, int $month, int $creatorId): Collection
    {
        return CreditNote::with('invoice.customer')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereHas('invoice', function ($query) use ($creatorId) {
                $query->where('created_by', $creatorId);
            })
            ->get();
    }
}
