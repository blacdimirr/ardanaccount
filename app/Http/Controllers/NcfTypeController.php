<?php

namespace App\Http\Controllers;

use App\Models\NcfType;
use Illuminate\Http\Request;

class NcfTypeController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('config_ncf_tipos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $types = NcfType::where(function ($query) {
            $query->where('created_by', \Auth::user()->creatorId())
                ->orWhere('created_by', 0);
        })
            ->orderBy('code')
            ->get();

        return view('ncf_types.index', compact('types'));
    }

    public function create()
    {
        if (!\Auth::user()->can('config_ncf_tipos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $ncfType = new NcfType();

        return view('ncf_types.create', compact('ncfType'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('config_ncf_tipos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validated = $this->validateType($request);

        NcfType::create($validated + ['created_by' => \Auth::user()->creatorId()]);

        return redirect()->route('ncf-types.index')->with('success', __('Tipo de NCF creado correctamente.'));
    }

    public function edit(NcfType $ncfType)
    {
        if (!$this->canManage($ncfType)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('ncf_types.edit', compact('ncfType'));
    }

    public function update(Request $request, NcfType $ncfType)
    {
        if (!$this->canManage($ncfType)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validated = $this->validateType($request, $ncfType->id);
        $ncfType->update($validated);

        return redirect()->route('ncf-types.index')->with('success', __('Tipo de NCF actualizado.'));
    }

    public function destroy(NcfType $ncfType)
    {
        if (!$this->canManage($ncfType)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ncfType->active = false;
        $ncfType->save();

        return redirect()->route('ncf-types.index')->with('success', __('Tipo de NCF desactivado.'));
    }

    protected function validateType(Request $request, ?int $typeId = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:10',
            'description' => 'required|string|max:255',
            'active' => 'sometimes|boolean',
        ]);
    }

    protected function canManage(NcfType $ncfType): bool
    {
        if (!\Auth::user()->can('config_ncf_tipos_manage')) {
            return false;
        }

        return in_array($ncfType->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
