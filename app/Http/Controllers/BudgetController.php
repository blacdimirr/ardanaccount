<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Budget;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use App\Models\Revenue;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->can('manage budget planner')) {
            $budgets = Budget::where('created_by', '=', \Auth::user()->creatorId())->get();
            $periods = Budget::$period;
            return view('budget.index', compact('budgets', 'periods'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('create budget planner')) {
            $periods = Budget::$period;

            $data['monthList'] = $month = $this->yearMonth(); // Monthly

            $data['quarterly_monthlist'] = [ // Quarterly (etiquetas para la vista de creación/edición)
                'Jan-Mar',
                'Apr-Jun',
                'Jul-Sep',
                'Oct-Dec',
            ];

            $data['half_yearly_monthlist'] = [ // Half - Yearly
                'Jan-Jun',
                'Jul-Dec',
            ];

            $data['yearly_monthlist'] = [ // Yearly
                'Jan-Dec',
            ];

            $data['yearList'] = $this->yearList();

            $incomeproduct  = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'income')->get();
            $expenseproduct = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'expense')->get();

            return view('budget.create', compact('periods', 'incomeproduct', 'expenseproduct'), $data);
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create budget planner')) {
            $validator = \Validator::make($request->all(), [
                'name'   => 'required',
                'period' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $budget               = new Budget();
            $budget->name         = $request->name;
            $budget->from         = $request->year;
            $budget->period       = $request->period;
            $budget->income_data  = json_encode($request->income);
            $budget->expense_data = json_encode($request->expense);
            $budget->created_by   = \Auth::user()->creatorId();
            $budget->save();

            // Notifications
            $setting = Utility::settings(\Auth::user()->creatorId());
            $budgetNotificationArr = [
                'budget_period' => \App\Models\Budget::$period[$request->period],
                'budget_year'   => $request->year,
                'budget_name'   => $request->name,
            ];

            if (isset($setting['budget_notification']) && $setting['budget_notification'] == 1) {
                Utility::send_slack_msg('new_budget', $budgetNotificationArr);
            }
            if (isset($setting['telegram_budget_notification']) && $setting['telegram_budget_notification'] == 1) {
                Utility::send_telegram_msg('new_budget', $budgetNotificationArr);
            }

            // webhook
            $module  = 'New Budget';
            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($budget);
                $status    = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->route('budget.index')->with('success', __('Budget planner successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }

            return redirect()->route('budget.index')->with('success', __('Budget planner successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Budget $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function show($ids)
    {
        if (\Auth::user()->can('view budget planner')) {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Budget Not Found.'));
            }

            $id                     = Crypt::decrypt($ids);
            $budget                 = Budget::find($id);
            $budget['income_data']  = json_decode($budget->income_data, true);
            $budgetTotalArrs        = !empty($budget['income_data']) ? array_values($budget['income_data']) : [];

            $budgetTotal = [];
            foreach ($budgetTotalArrs as $budgetTotalArr) {
                foreach ($budgetTotalArr as $k => $value) {
                    $budgetTotal[$k] = (isset($budgetTotal[$k]) ? $budgetTotal[$k] + $value : $value);
                }
            }

            $budget['expense_data']     = json_decode($budget->expense_data, true);
            $budgetExpenseTotalArrs     = !empty($budget['expense_data']) ? array_values($budget['expense_data']) : [];
            $budgetExpenseTotal         = [];
            foreach ($budgetExpenseTotalArrs as $budgetExpenseTotalArr) {
                foreach ($budgetExpenseTotalArr as $k => $value) {
                    $budgetExpenseTotal[$k] = (isset($budgetExpenseTotal[$k]) ? $budgetExpenseTotal[$k] + $value : $value);
                }
            }

            $data['monthList'] = $month = $this->yearMonth(); // Monthly

            // Para cálculos (rangos => etiquetas)
            $data['quarterly_monthlist'] = [
                '1-3'   => 'Jan-Mar',
                '4-6'   => 'Apr-Jun',
                '7-9'   => 'Jul-Sep',
                '10-12' => 'Oct-Dec',
            ];

            $data['half_yearly_monthlist'] = [
                '1-6'  => 'Jan-Jun',
                '7-12' => 'Jul-Dec',
            ];

            $data['yearly_monthlist'] = [
                '1-12' => 'Jan-Dec',
            ];

            $data['yearList'] = $this->yearList();
            $year = !empty($budget->from) ? $budget->from : date('Y');
            $data['currentYear'] = $year;

            $incomeproduct = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'income')->get();

            $incomeArr      = [];
            $incomeTotalArr = [];

            foreach ($incomeproduct as $cat) {
                if ($budget->period == 'monthly') {
                    $monthIncomeArr      = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $revenuAmount = Revenue::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $cat->id)
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) = ?', [$i])
                            ->sum('amount');

                        $revenuTotalAmount = Revenue::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) = ?', [$i])
                            ->sum('amount');

                        $invoices = Invoice::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $cat->id)
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) = ?', [$i])
                            ->with(['items'])
                            ->get();

                        $invoiceAmount = 0;
                        foreach ($invoices as $invoice) {
                            $invoiceAmount += $invoice->getTotal();
                        }

                        $invoicesTotal = Invoice::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) = ?', [$i])
                            ->get();

                        $invoiceTotalAmount = 0;
                        foreach ($invoicesTotal as $invoiceTotal) {
                            $invoiceTotalAmount += $invoiceTotal->getTotal();
                        }

                        $m = date("F", strtotime(date('Y-' . $i)));

                        $monthIncomeArr[$m]  = $invoiceAmount + $revenuAmount;
                        // Nota: este valor se reasigna por categoría; se mantiene la lógica original
                        $incomeTotalArr[$m]  = $invoiceTotalAmount + $revenuTotalAmount;
                    }
                    $incomeArr[$cat->id] = $monthIncomeArr;
                } else if ($budget->period == 'quarterly' || $budget->period == 'half-yearly' || $budget->period == 'yearly') {

                    if ($budget->period == 'quarterly') {
                        $durations = $data['quarterly_monthlist'];
                    } elseif ($budget->period == 'yearly') {
                        $durations = $data['yearly_monthlist'];
                    } else {
                        $durations = $data['half_yearly_monthlist'];
                    }

                    $monthIncomeArr = [];
                    foreach ($durations as $monthnumber => $monthName) {
                        $parts = explode('-', $monthnumber);

                        $revenuAmount = Revenue::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $cat->id)
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(date) <= ?', [$parts[1]])
                            ->sum('amount');

                        $revenuTotalAmount = Revenue::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(date) <= ?', [$parts[1]])
                            ->sum('amount');

                        $invoices = Invoice::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $cat->id)
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(send_date) <= ?', [$parts[1]])
                            ->get();

                        $invoiceAmount = 0;
                        foreach ($invoices as $invoice) {
                            $invoiceAmount += $invoice->getTotal();
                        }

                        $invoicesTotal = Invoice::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(send_date) <= ?', [$parts[1]])
                            ->get();

                        $invoiceTotalAmount = 0;
                        foreach ($invoicesTotal as $invoiceTotal) {
                            $invoiceTotalAmount += $invoiceTotal->getTotal();
                        }

                        $monthIncomeArr[$monthName] = $invoiceAmount + $revenuAmount;
                        $incomeTotalArr[$monthName] = $invoiceTotalAmount + $revenuTotalAmount;
                    }
                    $incomeArr[$cat->id] = $monthIncomeArr;
                }
            }

            $expenseproduct = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'expense')->get();

            $expenseArr      = [];
            $expenseTotalArr = [];

            foreach ($expenseproduct as $expense) {
                if ($budget->period == 'monthly') {
                    $monthExpenseArr       = [];
                    for ($i = 1; $i <= 12; $i++) {

                        $paymentAmount = Payment::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $expense->id)
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) = ?', [$i])
                            ->sum('amount');

                        $paymentTotalAmount = Payment::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) = ?', [$i])
                            ->sum('amount');

                        $bills = Bill::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $expense->id)
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) = ?', [$i])
                            ->with(['items', 'accounts'])
                            ->get();

                        $billAmount = 0;
                        foreach ($bills as $bill) {
                            $billAmount += $bill->getTotal();
                        }

                        $billsTotal = Bill::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) = ?', [$i])
                            ->get();

                        $billTotalAmount = 0;
                        foreach ($billsTotal as $billTotal) {
                            $billTotalAmount += $billTotal->getTotal();
                        }

                        $m = date("F", strtotime(date('Y-' . $i)));
                        $monthExpenseArr[$m]  = $billAmount + $paymentAmount;
                        // Nota: este valor se reasigna por categoría; se mantiene la lógica original
                        $expenseTotalArr[$m]  = $billTotalAmount + $paymentTotalAmount;
                    }
                    $expenseArr[$expense->id] = $monthExpenseArr;
                } else if ($budget->period == 'quarterly' || $budget->period == 'half-yearly' || $budget->period == 'yearly') {
                    if ($budget->period == 'quarterly') {
                        $durations = $data['quarterly_monthlist'];
                    } elseif ($budget->period == 'yearly') {
                        $durations = $data['yearly_monthlist'];
                    } else {
                        $durations = $data['half_yearly_monthlist'];
                    }

                    $monthExpenseArr = [];
                    foreach ($durations as $monthnumber => $monthName) {
                        $parts = explode('-', $monthnumber);

                        $paymentAmount = Payment::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $expense->id) // <- FIX: era $cat->id
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(date) <= ?', [$parts[1]])
                            ->sum('amount');

                        $paymentTotalAmount = Payment::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(date) = ?', [$year])
                            ->whereRaw('MONTH(date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(date) <= ?', [$parts[1]])
                            ->sum('amount');

                        $bills = Bill::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('category_id', $expense->id) // <- FIX: era $cat->id
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(send_date) <= ?', [$parts[1]])
                            ->get();

                        $billAmount = 0;
                        foreach ($bills as $bill) {
                            $billAmount += $bill->getTotal();
                        }

                        $billsTotal = Bill::where('created_by', '=', \Auth::user()->creatorId())
                            ->whereRaw('YEAR(send_date) = ?', [$year])
                            ->whereRaw('MONTH(send_date) >= ?', [$parts[0]])
                            ->whereRaw('MONTH(send_date) <= ?', [$parts[1]])
                            ->get();

                        $billTotalAmount = 0; // <- FIX nombre consistente
                        foreach ($billsTotal as $billTotal) {
                            $billTotalAmount += $billTotal->getTotal();
                        }

                        $monthExpenseArr[$monthName] = $billAmount + $paymentAmount;
                        $expenseTotalArr[$monthName] = $billTotalAmount + $paymentTotalAmount;
                    }
                    $expenseArr[$expense->id] = $monthExpenseArr;
                }
            }

            // ===== Normalizar claves para evitar "Undefined array key" =====
            $labelSets = [
                'monthly'     => $data['monthList'],                              // ['January', ...]
                'quarterly'   => array_values($data['quarterly_monthlist']),     // ['Jan-Mar', ...]
                'half-yearly' => array_values($data['half_yearly_monthlist']),   // ['Jan-Jun','Jul-Dec']
                'yearly'      => array_values($data['yearly_monthlist']),        // ['Jan-Dec']
            ];
            $labels = $labelSets[$budget->period] ?? $data['monthList'];

            $ensureKeys = function (array $arr) use ($labels) {
                return array_replace(array_fill_keys($labels, 0), $arr);
            };

            // Plan (budget)
            $budgetTotal        = $ensureKeys($budgetTotal ?? []);
            $budgetExpenseTotal = $ensureKeys($budgetExpenseTotal ?? []);

            // Real (actual)
            $incomeTotalArr  = $ensureKeys($incomeTotalArr ?? []);
            $expenseTotalArr = $ensureKeys($expenseTotalArr ?? []);

            // Por categoría
            foreach ($incomeArr as $k => $arr) {
                $incomeArr[$k] = $ensureKeys($arr ?? []);
            }
            foreach ($expenseArr as $k => $arr) {
                $expenseArr[$k] = $ensureKeys($arr ?? []);
            }

            // Compartir etiquetas normalizadas para la Blade (opcional si mantienes tus listas actuales)
            $data['periodLabels'] = $labels;

            // ===== Utilidades (fuera de los loops) =====
            $budgetprofit = [];
            foreach (array_keys($budgetTotal + $budgetExpenseTotal) as $k) {
                $budgetprofit[$k] = ($budgetTotal[$k] ?? 0) - ($budgetExpenseTotal[$k] ?? 0);
            }
            $data['budgetprofit'] = $budgetprofit;

            $actualprofit = [];
            foreach (array_keys($incomeTotalArr + $expenseTotalArr) as $k) {
                $actualprofit[$k] = ($incomeTotalArr[$k] ?? 0) - ($expenseTotalArr[$k] ?? 0);
            }
            $data['actualprofit'] = $actualprofit;

            return view(
                'budget.show',
                compact(
                    'id',
                    'budget',
                    'incomeproduct',
                    'expenseproduct',
                    'incomeArr',
                    'expenseArr',
                    'incomeTotalArr',
                    'expenseTotalArr',
                    'budgetTotal',
                    'budgetExpenseTotal'
                ),
                $data
            );
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Budget $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($ids)
    {
        if (\Auth::user()->can('edit budget planner')) {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Budget Not Found.'));
            }
            $id     = Crypt::decrypt($ids);
            $budget = Budget::find($id);

            $budget['income_data']  = json_decode($budget->income_data, true);
            $budget['expense_data'] = json_decode($budget->expense_data, true);

            $periods = Budget::$period;

            $data['monthList'] = $month = $this->yearMonth(); // Monthly

            $data['quarterly_monthlist'] = [ // Para UI de edición
                'Jan-Mar',
                'Apr-Jun',
                'Jul-Sep',
                'Oct-Dec',
            ];

            $data['half_yearly_monthlist'] = [
                'Jan-Jun',
                'Jul-Dec',
            ];

            $data['yearly_monthlist'] = [
                'Jan-Dec',
            ];

            $data['yearList'] = $this->yearList();
            if (!array_key_exists($budget->from, $data['yearList'])) {
                $data['yearList'] = [$budget->from => $budget->from] + $data['yearList'];
            }

            $incomeproduct  = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'income')->get();
            $expenseproduct = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'expense')->get();

            return view('budget.edit', compact('periods', 'budget', 'incomeproduct', 'expenseproduct'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Budget       $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Budget $budget)
    {
        if (\Auth::user()->can('edit budget planner')) {
            if ($budget->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make($request->all(), [
                    'name'   => 'required',
                    'period' => 'required',
                ]);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $budget->name         = $request->name;
                $budget->from         = $request->year;
                $budget->period       = $request->period;
                $budget->income_data  = json_encode($request->income);
                $budget->expense_data = json_encode($request->expense);
                $budget->save();

                return redirect()->route('budget.index')->with('success', __('Budget planner successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Budget $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Budget $budget)
    {
        if (\Auth::user()->can('delete budget planner')) {
            if ($budget->created_by == \Auth::user()->creatorId()) {
                $budget->delete();
                return redirect()->route('budget.index')->with('success', __('Budget Planner successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function yearMonth()
    {
        $month[] = 'January';
        $month[] = 'February';
        $month[] = 'March';
        $month[] = 'April';
        $month[] = 'May';
        $month[] = 'June';
        $month[] = 'July';
        $month[] = 'August';
        $month[] = 'September';
        $month[] = 'October';
        $month[] = 'November';
        $month[] = 'December';

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('+5 year'));
        $ending_year   = date('Y');

        foreach (range($ending_year, $starting_year) as $year) {
            $years[$year] = $year;
        }

        return $years;
    }
}
