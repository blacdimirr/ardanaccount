<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function create()
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $project = new Project(['active' => true]);
        $programs = $this->availablePrograms();

        return view('budget-classifiers.projects.create', compact('project', 'programs'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $data['created_by'] = \Auth::user()->creatorId();

        Project::create($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Proyecto creado correctamente.'));
    }

    public function edit(Project $project)
    {
        if (!$this->canManage($project)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $programs = $this->availablePrograms();

        return view('budget-classifiers.projects.edit', compact('project', 'programs'));
    }

    public function update(Request $request, Project $project)
    {
        if (!$this->canManage($project)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $project->update($data);

        return redirect()->route('budget-classifiers.index')->with('success', __('Proyecto actualizado.'));
    }

    public function destroy(Project $project)
    {
        if (!$this->canManage($project)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $project->active = false;
        $project->save();

        return redirect()->route('budget-classifiers.index')->with('success', __('Proyecto desactivado.'));
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'program_id' => 'required|exists:programas,id',
            'active' => 'sometimes|boolean',
        ]);
    }

    protected function availablePrograms()
    {
        $creatorId = \Auth::user()->creatorId();

        return Program::where(function ($query) use ($creatorId) {
            $query->where('created_by', $creatorId)
                ->orWhere('created_by', 0);
        })
            ->where('active', true)
            ->orderBy('code')
            ->pluck('name', 'id');
    }

    protected function canManage(Project $project): bool
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return false;
        }

        return in_array($project->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
