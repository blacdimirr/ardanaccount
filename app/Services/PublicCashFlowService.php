<?php

namespace App\Services;

use App\Models\PublicCashFlowMapping;
use App\Models\TransactionLines;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicCashFlowService
{
    public function buildReport(int $creatorId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $sections = [
            'operating' => [
                'label' => __('Operating Activities'),
                'lines' => [],
                'total' => 0,
            ],
            'investing' => [
                'label' => __('Investing Activities'),
                'lines' => [],
                'total' => 0,
            ],
            'financing' => [
                'label' => __('Financing Activities'),
                'lines' => [],
                'total' => 0,
            ],
        ];

        $mappings = PublicCashFlowMapping::query()
            ->where('created_by', $creatorId)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('line_name')
            ->get();

        if ($mappings->isEmpty()) {
            return $this->finalizeSections($sections);
        }

        $accountIds = $mappings->pluck('chart_of_account_id')->filter()->unique()->values();
        $balances = $this->resolveBalances($creatorId, $start, $end, $accountIds->all());

        foreach ($mappings as $mapping) {
            $sectionKey = $mapping->section;
            if (!isset($sections[$sectionKey])) {
                continue;
            }

            $lineKey = $mapping->line_name;
            if (!isset($sections[$sectionKey]['lines'][$lineKey])) {
                $sections[$sectionKey]['lines'][$lineKey] = [
                    'name' => $mapping->line_name,
                    'total' => 0,
                    'sort_order' => $mapping->sort_order,
                ];
            } else {
                $sections[$sectionKey]['lines'][$lineKey]['sort_order'] = min(
                    $sections[$sectionKey]['lines'][$lineKey]['sort_order'],
                    $mapping->sort_order
                );
            }

            $balance = $balances[$mapping->chart_of_account_id] ?? 0;
            $sections[$sectionKey]['lines'][$lineKey]['total'] += $balance;
        }

        return $this->finalizeSections($sections);
    }

    private function resolveBalances(int $creatorId, Carbon $start, Carbon $end, array $accountIds): array
    {
        if (empty($accountIds)) {
            return [];
        }

        $rows = TransactionLines::query()
            ->select('account_id', DB::raw('sum(credit) as total_credit'), DB::raw('sum(debit) as total_debit'))
            ->where('created_by', $creatorId)
            ->whereBetween('date', [$start, $end])
            ->whereIn('account_id', $accountIds)
            ->groupBy('account_id')
            ->get();

        $balances = [];
        foreach ($rows as $row) {
            $balances[$row->account_id] = (float) $row->total_credit - (float) $row->total_debit;
        }

        return $balances;
    }

    private function finalizeSections(array $sections): array
    {
        foreach ($sections as $key => $section) {
            $lines = $section['lines'];
            usort($lines, function ($a, $b) {
                if ($a['sort_order'] === $b['sort_order']) {
                    return strcmp($a['name'], $b['name']);
                }

                return $a['sort_order'] <=> $b['sort_order'];
            });

            $sections[$key]['lines'] = $lines;
            $sections[$key]['total'] = array_sum(array_column($lines, 'total'));
        }

        $sections['totals'] = [
            'net_cash' => $sections['operating']['total'] + $sections['investing']['total'] + $sections['financing']['total'],
        ];

        return $sections;
    }
}
