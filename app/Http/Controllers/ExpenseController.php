<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensesCategory;
use App\Models\NcfSeries;
use App\Models\NcfType;
use App\Models\Projects;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\NcfException;
use App\Services\NcfAssignmentService;

class ExpenseController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage expense') || \Auth::user()->type == 'client')
        {
            if(\Auth::user()->type == 'client')
            {
                $expenses = Expense::select('expenses.*','projects.name')->join('projects','projects.id','=','expenses.project')->where('projects.client','=',\Auth::user()->id)->where('expenses.created_by', '=', \Auth::user()->creatorId())->get();
            }
            else
            {
                $expenses = Expense::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('expenses.index')->with('expenses', $expenses);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create expense'))
        {
            $category = ExpensesCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $projects = \Auth::user()->projects->pluck('name', 'id');
            $users    = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', '!=', 'client')->get()->pluck('name', 'id');

            $ncfTypes = NcfType::where(function ($query) {
                $query->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('created_by', 0);
            })->pluck('code', 'id');
            $ncfTypes->prepend(__('Select NCF Type'), '');

            $ncfSeries = NcfSeries::with('type')->where(function ($query) {
                $query->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('created_by', 0);
            })->get()->mapWithKeys(function ($series) {
                $label = trim((optional($series->type)->code ? $series->type->code . ' - ' : '') . ($series->series ?? __('Series')));
                $range = $series->start_number . ' - ' . $series->end_number;

                return [$series->id => $label . ' (' . $range . ')'];
            });
            $ncfSeries->prepend(__('Select NCF Series'), '');

            return view('expenses.create', compact('category', 'projects', 'users', 'ncfTypes', 'ncfSeries'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create expense'))
        {

            $rules = [
                'category_id' => 'required',
                'amount' => 'required',
                'date' => 'required',
                'project_id' => 'required',
                'ncf_type_id' => 'nullable|exists:ncf_types,id',
                'ncf_series_id' => 'nullable|exists:ncf_series,id',
                'ncf_number' => 'nullable|string',
            ];
            if($request->attachment)
            {
                $rules['attachment'] = 'required|max:2048';
            }

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('expenses.index')->with('error', $messages->first());
            }

            $ncfData = null;
            $shouldAssignNcf = $request->filled('ncf_series_id') && empty($request->ncf_number);
            if ($shouldAssignNcf) {
                try {
                    $ncfData = app(NcfAssignmentService::class)->assignNextNumber(
                        (int) $request->ncf_series_id,
                        $request->ncf_type_id ? (int) $request->ncf_type_id : null
                    );
                } catch (NcfException $exception) {
                    return redirect()->back()->withInput()->with('error', $exception->getMessage());
                }
            }

            $expense              = new Expense();
            $expense->category_id = $request->category_id;
            $expense->description = $request->description;
            $expense->ncf_type_id = $request->ncf_type_id;
            $expense->ncf_series_id = $request->ncf_series_id;
            $expense->ncf_number  = $request->ncf_number;
            $expense->amount      = $request->amount;
            $expense->date        = $request->date;
            $expense->project     = $request->project_id;
            $expense->user_id     = $request->user_id;
            $expense->created_by  = \Auth::user()->creatorId();
            $expense->save();

            if($request->attachment)
            {
                $imageName = 'expense_' . $expense->id . "_" . $request->attachment->getClientOriginalName();
                $request->attachment->storeAs('public/attachment', $imageName);
                $expense->attachment = $imageName;
                $expense->save();
            }

            return redirect()->route('expenses.index')->with('success', __('Expense successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(Expense $expense)
    {
        if(\Auth::user()->can('edit expense'))
        {
            if($expense->created_by == \Auth::user()->creatorId())
            {
                $category = ExpensesCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $projects = \Auth::user()->projects->pluck('name', 'id');
                $users    = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', '!=', 'client')->get()->pluck('name', 'id');

                $ncfTypes = NcfType::where(function ($query) {
                    $query->where('created_by', \Auth::user()->creatorId())
                        ->orWhere('created_by', 0);
                })->pluck('code', 'id');
                $ncfTypes->prepend(__('Select NCF Type'), '');

                $ncfSeries = NcfSeries::with('type')->where(function ($query) {
                    $query->where('created_by', \Auth::user()->creatorId())
                        ->orWhere('created_by', 0);
                })->get()->mapWithKeys(function ($series) {
                    $label = trim((optional($series->type)->code ? $series->type->code . ' - ' : '') . ($series->series ?? __('Series')));
                    $range = $series->start_number . ' - ' . $series->end_number;

                    return [$series->id => $label . ' (' . $range . ')'];
                });
                $ncfSeries->prepend(__('Select NCF Series'), '');

                return view('expenses.edit', compact('expense', 'category', 'projects', 'users', 'ncfTypes', 'ncfSeries'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, Expense $expense)
    {
        if(\Auth::user()->can('edit expense'))
        {

            if($expense->created_by == \Auth::user()->creatorId())
            {

                $rules = [
                    'category_id' => 'required',
                    'amount' => 'required',
                    'date' => 'required',
                    'project_id' => 'required',
                    'ncf_type_id' => 'nullable|exists:ncf_types,id',
                    'ncf_series_id' => 'nullable|exists:ncf_series,id',
                    'ncf_number' => 'nullable|string',
                ];
                if($request->attachment)
                {
                    $rules['attachment'] = 'required|max:2048';
                }

                $validator = \Validator::make($request->all(), $rules);

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('expenses.index')->with('error', $messages->first());
                }

                $ncfData = null;
                $shouldAssignNcf = $request->filled('ncf_series_id')
                    && empty($request->ncf_number)
                    && ((int) $request->ncf_series_id !== (int) $expense->ncf_series_id || empty($expense->ncf_number));

                if ($shouldAssignNcf) {
                    try {
                        $ncfData = app(NcfAssignmentService::class)->assignNextNumber(
                            (int) $request->ncf_series_id,
                            $request->ncf_type_id ? (int) $request->ncf_type_id : null
                        );
                    } catch (NcfException $exception) {
                        return redirect()->back()->withInput()->with('error', $exception->getMessage());
                    }
                }
                $expense->category_id = $request->category_id;
                $expense->description = $request->description;
                $expense->ncf_type_id = $request->ncf_type_id;
                $expense->ncf_series_id = $request->ncf_series_id;
                $expense->ncf_number  = $request->ncf_number;
                $expense->amount      = $request->amount;
                $expense->date        = $request->date;
                $expense->project     = $request->project_id;
                $expense->user_id     = $request->user_id;
                $expense->save();

                if($request->attachment)
                {
                    if($expense->attachment)
                    {
                        \File::delete(storage_path('uploads/attachment/' . $expense->attachment));
                    }
                    $imageName = 'expense_' . $expense->id . "_" . $request->attachment->getClientOriginalName();
                    $request->attachment->storeAs('attachment', $imageName);
                    $expense->attachment = $imageName;
                    $expense->save();
                }

                return redirect()->route('expenses.index')->with('success', __('Expense successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Expense $expense)
    {
        if(\Auth::user()->can('delete expense'))
        {
            if($expense->created_by == \Auth::user()->creatorId())
            {
                $expense->delete();
                return redirect()->route('expenses.index')->with('success', __('Expense successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
