<?php

namespace App\Http\Controllers;

use App\Models\ProductServiceCategory;
use App\Models\RetentionRule;
use Illuminate\Http\Request;

class RetentionRuleController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('config_retenciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rules = RetentionRule::with('serviceCategory')
            ->where(function ($query) {
                $query->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('created_by', 0);
            })
            ->orderByDesc('active')
            ->orderBy('supplier_type')
            ->get();

        return view('retention_rules.index', compact('rules'));
    }

    public function create()
    {
        if (!\Auth::user()->can('config_retenciones_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $categories = $this->categoryOptions();

        return view('retention_rules.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('config_retenciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validated = $this->validateRule($request);

        RetentionRule::create($validated + ['created_by' => \Auth::user()->creatorId()]);

        return redirect()->route('retention-rules.index')->with('success', __('Regla de retención creada correctamente.'));
    }

    public function edit(RetentionRule $retentionRule)
    {
        if (!$this->canManageRule($retentionRule)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $categories = $this->categoryOptions();

        return view('retention_rules.edit', compact('retentionRule', 'categories'));
    }

    public function update(Request $request, RetentionRule $retentionRule)
    {
        if (!$this->canManageRule($retentionRule)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validated = $this->validateRule($request);
        $retentionRule->update($validated);

        return redirect()->route('retention-rules.index')->with('success', __('Regla de retención actualizada.'));
    }

    public function destroy(RetentionRule $retentionRule)
    {
        if (!$this->canManageRule($retentionRule)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $retentionRule->active = false;
        $retentionRule->save();

        return redirect()->route('retention-rules.index')->with('success', __('Regla desactivada.'));
    }

    protected function categoryOptions()
    {
        return ProductServiceCategory::where(function ($query) {
            $query->where('created_by', \Auth::user()->creatorId())
                ->orWhere('created_by', 0);
        })
            ->where('type', 'expense')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('Todas las categorías'), '');
    }

    protected function validateRule(Request $request): array
    {
        $request->merge([
            'service_category_id' => $request->service_category_id ?: null,
            'supplier_type' => $request->supplier_type ?: null,
        ]);

        return $request->validate([
            'supplier_type' => 'nullable|string|max:100',
            'service_category_id' => 'nullable|integer|exists:product_service_categories,id',
            'itbis_retention_rate' => 'required|numeric|min:0|max:100',
            'isr_retention_rate' => 'required|numeric|min:0|max:100',
            'active' => 'sometimes|boolean',
        ]);
    }

    protected function canManageRule(RetentionRule $retentionRule): bool
    {
        if (!\Auth::user()->can('config_retenciones_manage')) {
            return false;
        }

        return in_array($retentionRule->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
