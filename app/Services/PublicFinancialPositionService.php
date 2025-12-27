<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\PublicFinancialStatementMapping;
use App\Models\TransactionLines;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicFinancialPositionService
{
    public function buildReport(int $creatorId, string $cutoffDate): array
    {
        $cutoff = Carbon::parse($cutoffDate)->endOfDay();
        $sections = [
            'assets' => [
                'label' => __('Assets'),
                'lines' => [],
                'total' => 0,
            ],
            'liabilities' => [
                'label' => __('Liabilities'),
                'lines' => [],
                'total' => 0,
            ],
            'equity' => [
                'label' => __('Equity'),
                'lines' => [],
                'total' => 0,
            ],
        ];

        $mappings = PublicFinancialStatementMapping::query()
            ->where('created_by', $creatorId)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('line_name')
            ->get();

        if ($mappings->isEmpty()) {
            $this->buildDefaultLines($creatorId, $cutoff, $sections);

            return $this->finalizeSections($sections);
        }

        $accountIds = $mappings->pluck('chart_of_account_id')->filter()->unique()->values();
        $balances = $this->resolveBalances($creatorId, $cutoff, $accountIds->all());

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

    private function buildDefaultLines(int $creatorId, Carbon $cutoff, array &$sections): void
    {
        $types = ChartOfAccountType::query()
            ->where('created_by', $creatorId)
            ->whereIn('name', ['Assets', 'Liabilities', 'Equity'])
            ->get();

        foreach ($types as $type) {
            $sectionKey = match ($type->name) {
                'Assets' => 'assets',
                'Liabilities' => 'liabilities',
                'Equity' => 'equity',
                default => null,
            };

            if (!$sectionKey) {
                continue;
            }

            $accountIds = ChartOfAccount::query()
                ->where('created_by', $creatorId)
                ->where('type', $type->id)
                ->pluck('id')
                ->all();

            $total = 0;
            if (!empty($accountIds)) {
                $balances = $this->resolveBalances($creatorId, $cutoff, $accountIds);
                $total = array_sum($balances);
            }

            $sections[$sectionKey]['lines'][$type->name] = [
                'name' => __($type->name),
                'total' => $total,
                'sort_order' => 0,
            ];
        }
    }

    private function resolveBalances(int $creatorId, Carbon $cutoff, array $accountIds): array
    {
        if (empty($accountIds)) {
            return [];
        }

        $rows = TransactionLines::query()
            ->select('account_id', DB::raw('sum(credit) as total_credit'), DB::raw('sum(debit) as total_debit'))
            ->where('created_by', $creatorId)
            ->whereDate('date', '<=', $cutoff)
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
            'assets' => $sections['assets']['total'],
            'liabilities' => $sections['liabilities']['total'],
            'equity' => $sections['equity']['total'],
            'liabilities_equity' => $sections['liabilities']['total'] + $sections['equity']['total'],
        ];

        return $sections;
    }
}
