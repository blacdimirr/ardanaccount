<?php

namespace App\Http\Controllers;

use App\Models\ClasificadorObjetoGasto;
use App\Models\FundingSource;
use App\Models\Program;
use App\Models\Project;

class BudgetClassifierController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('config_clasificadores_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();

        $objects = ClasificadorObjetoGasto::where(function ($query) use ($creatorId) {
            $query->where('created_by', $creatorId)
                ->orWhere('created_by', 0);
        })
            ->orderBy('code')
            ->get();

        $fundingSources = FundingSource::where(function ($query) use ($creatorId) {
            $query->where('created_by', $creatorId)
                ->orWhere('created_by', 0);
        })
            ->orderBy('code')
            ->get();

        $programs = Program::where(function ($query) use ($creatorId) {
            $query->where('created_by', $creatorId)
                ->orWhere('created_by', 0);
        })
            ->orderBy('code')
            ->get();

        $projects = Project::with('program')
            ->where(function ($query) use ($creatorId) {
                $query->where('created_by', $creatorId)
                    ->orWhere('created_by', 0);
            })
            ->orderBy('code')
            ->get();

        return view('budget-classifiers.index', compact('objects', 'fundingSources', 'programs', 'projects'));
    }
}
