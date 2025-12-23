<?php

namespace App\Http\Controllers;

use App\Models\FundingSource;
use Illuminate\Http\Request;

class FundingSourceController extends Controller
{
    public function create()
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $fundingSource = new FundingSource(['active' => true]);

        return view('budget-classifiers.funding-sources.create', compact('fundingSource'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $data['created_by'] = \Auth::user()->creatorId();

        FundingSource::create($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Fuente de financiamiento creada correctamente.'));
    }

    public function edit(FundingSource $fundingSource)
    {
        if (!$this->canManage($fundingSource)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('budget-classifiers.funding-sources.edit', compact('fundingSource'));
    }

    public function update(Request $request, FundingSource $fundingSource)
    {
        if (!$this->canManage($fundingSource)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $fundingSource->update($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Fuente de financiamiento actualizada.'));
    }

    public function destroy(FundingSource $fundingSource)
    {
        if (!$this->canManage($fundingSource)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $fundingSource->active = false;
        $fundingSource->save();

        return redirect()->route('budget-classifiers.index')->with('success', __('Fuente de financiamiento desactivada.'));
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'code' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'active' => 'sometimes|boolean',
        ]);
    }

    protected function canManage(FundingSource $fundingSource): bool
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return false;
        }

        return in_array($fundingSource->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
