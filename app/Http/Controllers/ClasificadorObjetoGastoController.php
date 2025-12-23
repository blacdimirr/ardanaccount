<?php

namespace App\Http\Controllers;

use App\Models\ClasificadorObjetoGasto;
use Illuminate\Http\Request;

class ClasificadorObjetoGastoController extends Controller
{
    public function create()
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $classifier = new ClasificadorObjetoGasto(['active' => true]);

        return view('budget-classifiers.objects.create', compact('classifier'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $data['created_by'] = \Auth::user()->creatorId();

        ClasificadorObjetoGasto::create($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Objeto del gasto creado correctamente.'));
    }

    public function edit(ClasificadorObjetoGasto $object)
    {
        if (!$this->canManage($object)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('budget-classifiers.objects.edit', ['classifier' => $object]);
    }

    public function update(Request $request, ClasificadorObjetoGasto $object)
    {
        if (!$this->canManage($object)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $object->update($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Objeto del gasto actualizado.'));
    }

    public function destroy(ClasificadorObjetoGasto $object)
    {
        if (!$this->canManage($object)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $object->active = false;
        $object->save();

        return redirect()->route('budget-classifiers.index')->with('success', __('Objeto del gasto desactivado.'));
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'code' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'level' => 'required|integer|min:1',
            'active' => 'sometimes|boolean',
        ]);
    }

    protected function canManage(ClasificadorObjetoGasto $object): bool
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return false;
        }

        return in_array($object->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
