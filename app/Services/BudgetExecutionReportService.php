<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\ProductServiceCategory;
use Illuminate\Support\Arr;

class BudgetExecutionReportService
{
    public const CLASSIFIER_OBJECT = 'objeto_gasto';
    public const CLASSIFIER_PROGRAM = 'programa';
    public const CLASSIFIER_PROJECT = 'proyecto';
    public const CLASSIFIER_SOURCE = 'fuente';

    public function reporteEjecucion(int $creatorId, array $filters = []): array
    {
        $classifier = $filters['classifier'] ?? self::CLASSIFIER_OBJECT;
        $budget = $this->resolveBudget($creatorId, $filters);

        $rows = [];
        $totals = [
            'pia' => 0,
            'pim' => 0,
            'compromiso' => 0,
            'devengado' => 0,
            'pagado' => 0,
        ];

        if (!$budget) {
            return [
                'budget' => null,
                'classifier' => $classifier,
                'rows' => [],
                'totals' => $totals,
                'budgetLabel' => __('No budget selected'),
            ];
        }

        $categories = ProductServiceCategory::where('created_by', $creatorId)
            ->where('type', 'expense')
            ->with(['objetoGasto', 'programa', 'proyecto', 'fuenteFinanciamiento'])
            ->get();

        $piaTotals = $this->normalizeTotals($budget->monto_pia);
        $pimTotals = $this->normalizeTotals($budget->monto_pim);
        $committedTotals = $this->normalizeTotals($budget->monto_comprometido);
        $accruedTotals = $this->normalizeTotals($budget->monto_devengado);
        $paidTotals = $this->normalizeTotals($budget->monto_pagado);

        foreach ($categories as $category) {
            $group = $this->classifierData($classifier, $category);
            $key = $group['key'];
            $label = $group['label'];

            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'label' => $label,
                    'pia' => 0,
                    'pim' => 0,
                    'compromiso' => 0,
                    'devengado' => 0,
                    'pagado' => 0,
                ];
            }

            $categoryId = $category->id;
            $rows[$key]['pia'] += (float) data_get($piaTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['pim'] += (float) data_get($pimTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['compromiso'] += (float) data_get($committedTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['devengado'] += (float) data_get($accruedTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['pagado'] += (float) data_get($paidTotals, 'expense.' . $categoryId, 0);
        }

        $rows = collect($rows)
            ->sortBy('label')
            ->values()
            ->all();

        foreach ($rows as $row) {
            $totals['pia'] += $row['pia'];
            $totals['pim'] += $row['pim'];
            $totals['compromiso'] += $row['compromiso'];
            $totals['devengado'] += $row['devengado'];
            $totals['pagado'] += $row['pagado'];
        }

        return [
            'budget' => $budget,
            'classifier' => $classifier,
            'rows' => $rows,
            'totals' => $totals,
            'budgetLabel' => $this->formatBudgetLabel($budget),
        ];
    }

    public function classifierOptions(): array
    {
        return [
            self::CLASSIFIER_OBJECT => __('Object of expenditure'),
            self::CLASSIFIER_PROGRAM => __('Program'),
            self::CLASSIFIER_PROJECT => __('Project'),
            self::CLASSIFIER_SOURCE => __('Funding Source'),
        ];
    }

    public function publicClassifierOptions(): array
    {
        return [
            self::CLASSIFIER_OBJECT => __('Object of expenditure'),
            self::CLASSIFIER_PROGRAM => __('Program'),
            self::CLASSIFIER_SOURCE => __('Funding Source'),
        ];
    }

    public function estadoEjecucion(int $creatorId, array $filters = []): array
    {
        $classifier = $filters['classifier'] ?? self::CLASSIFIER_OBJECT;
        $budget = $this->resolveBudget($creatorId, $filters);

        $rows = [];
        $totals = [
            'pia' => 0,
            'pim' => 0,
            'compromiso' => 0,
            'devengado' => 0,
            'pagado' => 0,
            'saldo' => 0,
        ];

        if (!$budget) {
            return [
                'budget' => null,
                'classifier' => $classifier,
                'rows' => [],
                'totals' => $totals,
                'budgetLabel' => __('No budget selected'),
            ];
        }

        $categories = ProductServiceCategory::where('created_by', $creatorId)
            ->where('type', 'expense')
            ->with(['objetoGasto', 'programa', 'fuenteFinanciamiento'])
            ->get();

        $piaTotals = $this->normalizeTotals($budget->monto_pia);
        $pimTotals = $this->normalizeTotals($budget->monto_pim);
        $committedTotals = $this->normalizeTotals($budget->monto_comprometido);
        $accruedTotals = $this->normalizeTotals($budget->monto_devengado);
        $paidTotals = $this->normalizeTotals($budget->monto_pagado);

        foreach ($categories as $category) {
            $group = $this->classifierData($classifier, $category);
            $key = $group['key'];
            $label = $group['label'];

            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'label' => $label,
                    'pia' => 0,
                    'pim' => 0,
                    'compromiso' => 0,
                    'devengado' => 0,
                    'pagado' => 0,
                    'saldo' => 0,
                ];
            }

            $categoryId = $category->id;
            $rows[$key]['pia'] += (float) data_get($piaTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['pim'] += (float) data_get($pimTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['compromiso'] += (float) data_get($committedTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['devengado'] += (float) data_get($accruedTotals, 'expense.' . $categoryId, 0);
            $rows[$key]['pagado'] += (float) data_get($paidTotals, 'expense.' . $categoryId, 0);
        }

        $rows = collect($rows)
            ->map(function (array $row) {
                $row['saldo'] = $row['pim'] - $row['pagado'];
                return $row;
            })
            ->sortBy('label')
            ->values()
            ->all();

        foreach ($rows as $row) {
            $totals['pia'] += $row['pia'];
            $totals['pim'] += $row['pim'];
            $totals['compromiso'] += $row['compromiso'];
            $totals['devengado'] += $row['devengado'];
            $totals['pagado'] += $row['pagado'];
            $totals['saldo'] += $row['saldo'];
        }

        return [
            'budget' => $budget,
            'classifier' => $classifier,
            'rows' => $rows,
            'totals' => $totals,
            'budgetLabel' => $this->formatBudgetLabel($budget),
        ];
    }

    public function formatBudgetLabel(?Budget $budget): string
    {
        if (!$budget) {
            return __('No budget selected');
        }

        $periodLabel = Budget::$period[$budget->period] ?? $budget->period;
        $year = $budget->from ?? ($budget->start_date ? date('Y', strtotime($budget->start_date)) : '');
        $name = trim($budget->name ?? '');

        return trim($name . ' - ' . $periodLabel . ' ' . $year);
    }

    protected function resolveBudget(int $creatorId, array $filters = []): ?Budget
    {
        $query = Budget::where('created_by', $creatorId);

        if (!empty($filters['budget_id'])) {
            return $query->where('id', $filters['budget_id'])->first();
        }

        if (!empty($filters['year'])) {
            $query->where('from', $filters['year']);
        }

        return $query->orderByDesc('from')->orderByDesc('id')->first();
    }

    protected function normalizeTotals($value): array
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
            $normalized[$type] = Arr::wrap($normalized[$type]);
        }

        return $normalized;
    }

    protected function classifierData(string $classifier, ProductServiceCategory $category): array
    {
        $label = __('Unassigned');
        $key = 'unassigned';

        if ($classifier === self::CLASSIFIER_OBJECT) {
            $object = $category->objetoGasto;
            if ($object) {
                $label = trim($object->code . ' ' . $object->description);
                $key = 'object_' . $object->id;
            }
        } elseif ($classifier === self::CLASSIFIER_PROGRAM) {
            $program = $category->programa;
            if ($program) {
                $label = trim($program->code . ' ' . $program->name);
                $key = 'program_' . $program->id;
            }
        } elseif ($classifier === self::CLASSIFIER_PROJECT) {
            $project = $category->proyecto;
            if ($project) {
                $label = trim($project->code . ' ' . $project->name);
                $key = 'project_' . $project->id;
            }
        } elseif ($classifier === self::CLASSIFIER_SOURCE) {
            $source = $category->fuenteFinanciamiento;
            if ($source) {
                $label = trim($source->code . ' ' . $source->description);
                $key = 'source_' . $source->id;
            }
        }

        return [
            'key' => $key,
            'label' => $label !== '' ? $label : __('Unassigned'),
        ];
    }
}
