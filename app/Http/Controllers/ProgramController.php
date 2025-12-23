<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function create()
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $program = new Program(['active' => true]);

        return view('budget-classifiers.programs.create', compact('program'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $data['created_by'] = \Auth::user()->creatorId();

        Program::create($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Programa creado correctamente.'));
    }

    public function edit(Program $program)
    {
        if (!$this->canManage($program)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('budget-classifiers.programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        if (!$this->canManage($program)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $program->update($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Programa actualizado.'));
    }

    public function destroy(Program $program)
    {
        if (!$this->canManage($program)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $program->active = false;
        $program->save();

        return redirect()->route('budget-classifiers.index')->with('success', __('Programa desactivado.'));
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'active' => 'sometimes|boolean',
        ]);
    }

    protected function canManage(Program $program): bool
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return false;
        }

        return in_array($program->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
