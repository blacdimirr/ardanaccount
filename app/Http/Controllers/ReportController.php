<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\Customer;
use App\Models\CuentaRecaudadora;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\JournalItem;
use App\Models\NominaPeriodo;
use App\Models\Payment;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Revenue;
use App\Models\Recaudacion;
use App\Models\StockReport;
use App\Models\Tax;
use App\Models\Vender;
use App\Models\Utility;
use App\Models\FondoRotatorio;
use App\Models\MovimientoFondo;
use App\Models\MovimientoBancario;
use App\Exports\AccountStatementExport;
use App\Exports\BalanceSheetExport;
use App\Exports\BudgetExecutionExport;
use App\Exports\FondoMovimientosExport;
use App\Exports\ConciliacionBancariaExport;
use App\Exports\EstadosCuentaConciliacionExport;
use App\Exports\RecaudacionesDiariasExport;
use App\Exports\PublicBudgetExecutionExport;
use App\Exports\ProductStockExport;
use App\Exports\ProfitLossExport;
use App\Exports\TrialBalancExport;
use App\Exports\PublicFinancialPositionExport;
use App\Exports\Dgii606Export;
use App\Exports\Dgii607Export;
use App\Exports\Dgii608Export;
use App\Exports\NominaCostosServicioExport;
use App\Services\Dgii606Service;
use App\Services\Dgii607Service;
use App\Services\Dgii608Service;
use App\Services\EstadoCuentaConciliacionService;
use App\Services\NominaAsientoService;
use App\Services\BudgetExecutionReportService;
use App\Services\PublicFinancialPositionService;
use App\Models\ChartOfAccountParent;
use App\Models\Status;
use App\Models\TransactionLines;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    public function incomeSummary(Request $request)
    {
        if (\Auth::user()->can('income report')) {
            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('All', '');
            $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $customer->prepend('Select Customer', '');
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'income')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            if ($request->period === 'quarterly') {
                $month = [
                    'January-March',
                    'April-June',
                    'July-September',
                    'Octomber-December',
                ];
            } elseif ($request->period === 'half-yearly') {
                $month = [
                    'January-June',
                    'July-December',
                ];
            } elseif ($request->period === 'yearly') {
                $month = array_values(array_reverse($this->yearList()));
            } else {
                $month = $this->yearMonth();
            }

            $data['monthList'] = $month;
            $data['yearList'] = $this->yearList();
            $data['periods']   = $this->period();
            $filter['category'] = __('All');
            $filter['customer'] = __('All');

            if ($request->period === 'yearly') {
                $year = array_reverse($this->yearList());
                $yearList = [];
                foreach ($year as $value) {
                    $yearList[$value] = $value;
                }
            } else {
                $yearList[($request->year) ? $request->year : date('Y')] = ($request->year) ? $request->year : date('Y');
            }

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            if (isset($request->period)) {
                $period = $request->period;
            } else {
                $period = 'monthly';
            }
            $data['currentYear'] = $year;

            // ------------------------------REVENUE INCOME-----------------------------------

            $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year, product_service_categories.name as category_id')->leftjoin('product_service_categories', 'revenues.category_id', '=', 'product_service_categories.id')->where('product_service_categories.type', '=', 'income');
            $incomes->where('revenues.created_by', '=', \Auth::user()->creatorId());
            if ($request->period != 'yearly') {
                $incomes->whereRAW('YEAR(date) =?', [$year]);
            }

            if (!empty($request->category)) {
                $incomes->where('category_id', '=', $request->category);
                $cat = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }

            if (!empty($request->customer)) {
                $incomes->where('customer_id', '=', $request->customer);
                $cust = Customer::find($request->customer);
                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }

            $incomes->groupBy('month', 'year', 'category_id');
            $incomes = $incomes->get();

            $tmpArray = [];
            foreach ($incomes as $income) {
                $tmpArray[$income->category_id][$income->year][$income->month] = $income->amount;
            }
            $array = [];

            foreach ($tmpArray as $key => $yearData) {
                $array[$key] = [];

                foreach ($yearList as $targetYear) {
                    $array[$key][$targetYear] = [];

                    for ($i = 1; $i <= 12; $i++) {
                        $array[$key][$targetYear][$i] = 0;
                    }

                    if (isset($yearData[$targetYear])) {
                        foreach ($yearData[$targetYear] as $month => $value) {
                            $array[$key][$targetYear][$month] = (float) $value; // Convert the value to float if needed
                        }
                    }
                }
            }

            //---------------------------INVOICE INCOME-----------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,product_service_categories.name as category_id,invoice_id,invoices.id')
                ->leftjoin('product_service_categories', 'invoices.category_id', '=', 'product_service_categories.id')
                ->where('invoices.created_by', \Auth::user()->creatorId())->where('status', '!=', 0);

            if ($request->period != 'yearly') {
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
            }

            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }

            if (!empty($request->category)) {
                $invoices->where('category_id', '=', $request->category);
            }

            $invoices = $invoices->get();

            // ------------------------------------------ invoice ------------------------------------------

            $invoiceTmpArray = [];

            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->year][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceArray = [];

            foreach ($invoiceTmpArray as $key => $yearData) {
                $invoiceArray[$key] = [];

                foreach ($yearList as $targetYear) {
                    $invoiceArray[$key][$targetYear] = [];

                    for ($i = 1; $i <= 12; $i++) {
                        $invoiceArray[$key][$targetYear][$i] = 0;
                    }

                    if (isset($yearData[$targetYear])) {
                        foreach ($yearData[$targetYear] as $month => $values) {
                            if (is_array($values)) {
                                $sum = array_sum($values);
                                $invoiceArray[$key][$targetYear][$month] = $sum;
                            } else {
                                $invoiceArray[$key][$targetYear][$month] = (float) $values;
                            }
                        }
                    }
                }
            }

            $invoicesum = Utility::billData($invoiceArray, $request, $yearList);

            $invoiceTotalArray = [];

            foreach ($invoices as $invoice) {
                $invoiceTotalArray[$invoice->year][$invoice->month][] = $invoice->getTotal();
            }
            // ------------------------------------------ income ------------------------------------------

            $incomeArr = [];
            $invoiceArr = [];
            $incomesum = [];

            foreach ($yearList as $year) {
                $invoiceArr[$year] = [];

                for ($i = 1; $i <= 12; $i++) {
                    $invoiceArr[$year][$i] = 0;
                }

                if (isset($invoiceTotalArray[$year])) {
                    foreach ($invoiceTotalArray[$year] as $month => $values) {
                        $invoiceArr[$year][$month] = array_sum($values);
                    }
                }
            }


            foreach ($array as $key => $categoryData) {

                $incomesum[] = Utility::expenseData($key, $categoryData, $request, $yearList);
            }

            $revenueTotalArray = [];

            foreach ($incomes as $income) {
                $revenueTotalArray[$income->year][$income->month][] = $income->amount;
            }

            foreach ($yearList as $year) {
                $incomeArr[$year] = [];

                for ($i = 1; $i <= 12; $i++) {
                    $incomeArr[$year][$i] = 0;
                }

                if (isset($revenueTotalArray[$year])) {
                    foreach ($revenueTotalArray[$year] as $month => $values) {
                        $incomeArr[$year][$month] = array_sum($values);
                    }
                }
            }


            $chartIncomeArr = Utility::totalData($invoiceArr, $incomeArr, $request, $yearList);


            $data['chartIncomeArr'] = $chartIncomeArr;
            $data['incomeArr'] = $incomesum;
            $data['invoiceArray'] = $invoicesum;
            $data['account'] = $account;
            $data['customer'] = $customer;
            $data['category'] = $category;
            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;

            return view('report.income_summary', compact('filter', 'category'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function expenseSummary(Request $request)
    {
        if (\Auth::user()->can('expense report')) {
            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');
            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'expense')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            if ($request->period === 'quarterly') {
                $month = [
                    'January-March',
                    'April-June',
                    'July-September',
                    'Octomber-December',
                ];
            } elseif ($request->period === 'half-yearly') {
                $month = [
                    'January-June',
                    'July-December',
                ];
            } elseif ($request->period === 'yearly') {
                $month = array_values(array_reverse($this->yearList()));
            } else {
                $month = $this->yearMonth();
            }
           
            $data['monthList'] = $month;
            $data['yearList'] = $this->yearList();
            $data['periods']   = $this->period();
            $filter['category'] = __('All');
            $filter['vender'] = __('All');
            if ($request->period === 'yearly') {
                $year = array_reverse($this->yearList());
                $yearList = [];
                foreach ($year as $value) {
                    $yearList[$value] = $value;
                }
                }
                else
                {
                $yearList[($request->year) ? $request->year : date('Y')] = ($request->year) ? $request->year : date('Y');
                }
            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;
            //   -----------------------------------------PAYMENT EXPENSE ------------------------------------------------------------
            $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,product_service_categories.name as category_id')->leftjoin('product_service_categories', 'payments.category_id', '=', 'product_service_categories.id');
            $expenses->where('payments.created_by', '=', \Auth::user()->creatorId());
            if ($request->period != 'yearly') {
                $expenses->whereRAW('YEAR(date) =?', [$year]);
            }
            if (!empty($request->category)) {
                $expenses->where('category_id', '=', $request->category);
                $cat = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }
            if (!empty($request->vender)) {
                $expenses->where('vender_id', '=', $request->vender);
                $vend = Vender::find($request->vender);
                $filter['vender'] = !empty($vend) ? $vend->name : '';
            }
            $expenses->groupBy('month', 'year', 'category_id');
            $expenses = $expenses->get();
            $tmpArray = [];
            foreach ($expenses as $expense) {
                $tmpArray[$expense->category_id][$expense->year][$expense->month] = $expense->amount;
            }
            $array = [];
            foreach ($tmpArray as $key => $yearData) {
                $array[$key] = [];
                foreach ($yearList as $targetYear) {
                    $array[$key][$targetYear] = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $array[$key][$targetYear][$i] = 0;
                    }
                    if (isset($yearData[$targetYear])) {
                        foreach ($yearData[$targetYear] as $month => $value) {
                            $array[$key][$targetYear][$month] = (float) $value; // Convert the value to float if needed
                        }
                    }
                }
            }
            //     ------------------------------------BILL EXPENSE----------------------------------------------------
            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,product_service_categories.name as category_id,bill_id, bills.id')
                ->leftjoin('product_service_categories', 'bills.category_id', '=', 'product_service_categories.id')
                ->where('bills.created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);
            if ($request->period != 'yearly') {
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
                    }
            if (!empty($request->vender)) {
                $bills->where('vender_id', '=', $request->vender);
            }
            if (!empty($request->category)) {
                $bills->where('category_id', '=', $request->category);
            }
            $bills = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->year][$bill->month][] = $bill->getTotal();
            }
            $billArray = [];
            foreach ($billTmpArray as $key => $yearData) {
                $billArray[$key] = [];
                foreach ($yearList as $targetYear) {
                    $billArray[$key][$targetYear] = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $billArray[$key][$targetYear][$i] = 0;
                    }
                    if (isset($yearData[$targetYear])) {
                        foreach ($yearData[$targetYear] as $month => $values) {
                            if (is_array($values)) {
                                $sum = array_sum($values);
                                $billArray[$key][$targetYear][$month] = $sum;
                            } else {
                                $billArray[$key][$targetYear][$month] = (float) $values;
                            }
                        }
                    }
                }
            }
            $billsum = Utility::billInvoiceData($billArray, $request , $yearList);
            $billTotalArray = [];
            foreach ($bills as $bill) {
                $billTotalArray[$bill->year][$bill->month][] = $bill->getTotal();
            }
            $expenseArr = [];
            $billArr = [];
            $expensesum = [];
        foreach ($yearList as $year) {
            $billArr[$year] = [];
            for ($i = 1; $i <= 12; $i++) {
                $billArr[$year][$i] = 0;
            }
            if (isset($billTotalArray[$year])) {
                foreach ($billTotalArray[$year] as $month => $values) {
                    $billArr[$year][$month] = array_sum($values);
                }
            }
        }
        foreach ($array as $key => $categoryData) {
            $expensesum[] = Utility::revenuePaymentData($key , $categoryData, $request ,$yearList);
        }
        $paymentTotalArray = [];
        foreach ($expenses as $expense) {
            $paymentTotalArray[$expense->year][$expense->month][] = $expense->amount;
        }
        foreach ($yearList as $year) {
            $expenseArr[$year] = [];
            for ($i = 1; $i <= 12; $i++) {
                $expenseArr[$year][$i] = 0;
            }
            if (isset($paymentTotalArray[$year])) {
                foreach ($paymentTotalArray[$year] as $month => $values) {
                    $expenseArr[$year][$month] = array_sum($values);
                }
            }
        }
            $chartExpenseArr = Utility::totalData($billArr, $expenseArr, $request , $yearList);
            $data['chartExpenseArr'] = $chartExpenseArr;
            $data['expenseArr'] = $expensesum;
            $data['billArray'] = $billsum;
            $data['account'] = $account;
            $data['vender'] = $vender;
            $data['category'] = $category;
            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;
            return view('report.expense_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function incomeVsExpenseSummary(Request $request)
    {
        if (\Auth::user()->can('income vs expense report')) {
            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');
            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');
            $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $customer->prepend('Select Customer', '');

            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->whereIn(
                'type', [
                    'income',
                    'expense',
                ]
            )->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            if ($request->period === 'quarterly') {
                $month = [
                    'January-March',
                    'April-June',
                    'July-September',
                    'Octomber-December',
                ];
            } elseif ($request->period === 'half-yearly') {
                $month = [
                    'January-June',
                    'July-December',
                ];
            } elseif ($request->period === 'yearly') {
                $month = array_values(array_reverse($this->yearList()));

            } else {
                $month = $this->yearMonth();
            }

            $data['monthList'] = $month;
            $data['yearList'] = $this->yearList();
            $data['periods']   = $this->period();
            $filter['category'] = __('All');
            $filter['customer'] = __('All');
            $filter['vender'] = __('All');

            if ($request->period === 'yearly') {
                $year = array_reverse($this->yearList());
                $yearList = [];
                foreach ($year as $value) {
                    $yearList[$value] = $value;
                }
                }
                else
                {
                $yearList[($request->year) ? $request->year : date('Y')] = ($request->year) ? $request->year : date('Y');
                }

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // ------------------------------TOTAL PAYMENT EXPENSE-----------------------------------------------------------
            $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $expensesData->where('payments.created_by', '=', \Auth::user()->creatorId());
            if ($request->period != 'yearly') {
                $expensesData->whereRAW('YEAR(date) =?', [$year]);
            }

            if (!empty($request->category)) {
                $expensesData->where('category_id', '=', $request->category);
                $cat = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';

            }
            if (!empty($request->vender)) {
                $expensesData->where('vender_id', '=', $request->vender);

                $vend = Vender::find($request->vender);
                $filter['vender'] = !empty($vend) ? $vend->name : '';
            }
            $expensesData->groupBy('month', 'year');
            $expensesData = $expensesData->get();

            // ------------------------------TOTAL BILL EXPENSE-----------------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            if ($request->period != 'yearly') {
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
            }

            if (!empty($request->vender)) {
                $bills->where('vender_id', '=', $request->vender);

            }

            if (!empty($request->category)) {
                $bills->where('category_id', '=', $request->category);
            }

            $bills = $bills->get();

            $paymentTotalArray = [];
            foreach ($expensesData as $expense) {
                $paymentTotalArray[$expense->year][$expense->month][] = $expense->amount;
            }
            $expenseArr = [];

            foreach ($yearList as $year) {
                $expenseArr[$year] = [];
            
                for ($i = 1; $i <= 12; $i++) {
                    $expenseArr[$year][$i] = 0;
                }
            
                if (isset($paymentTotalArray[$year])) {
                    foreach ($paymentTotalArray[$year] as $month => $values) {
                        $expenseArr[$year][$month] = array_sum($values);
                    }
                }
            }

            $billTotalArray = [];
            foreach ($bills as $bill) {
                $billTotalArray[$bill->year][$bill->month][] = $bill->getTotal();
            }

            $billArr = [];
            $expensesum = [];

            foreach ($yearList as $year) {
                $billArr[$year] = [];
            
                for ($i = 1; $i <= 12; $i++) {
                    $billArr[$year][$i] = 0;
                }
            
                if (isset($billTotalArray[$year])) {
                    foreach ($billTotalArray[$year] as $month => $values) {
                        $billArr[$year][$month] = array_sum($values);
                    }
                }
            }


            $billsum = Utility::totalSum($billArr, $request , $yearList);

            $expensesum = Utility::totalSum($expenseArr, $request , $yearList);

            $chartExpenseArr = Utility::totalData($billArr, $expenseArr, $request , $yearList);

            // ------------------------------TOTAL REVENUE INCOME-----------------------------------------------------------

            $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $incomesData->where('revenues.created_by', '=', \Auth::user()->creatorId());
            if ($request->period != 'yearly') {
                $incomesData->whereRAW('YEAR(date) =?', [$year]);
            }

            if (!empty($request->category)) {
                $incomesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->customer)) {
                $incomesData->where('customer_id', '=', $request->customer);
                $cust = Customer::find($request->customer);
                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $incomesData->groupBy('month', 'year');
            $incomesData = $incomesData->get();

            // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')
                ->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
                if ($request->period != 'yearly') {
                    $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                }
            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }
            if (!empty($request->category)) {
                $invoices->where('category_id', '=', $request->category);
            }
            $invoices = $invoices->get();

            $revenueTotalArray = [];
            foreach ($incomesData as $income) {
                $revenueTotalArray[$income->year][$income->month][] = $income->amount;
            }

            $incomeArr = [];

            foreach ($yearList as $year) {
                $incomeArr[$year] = [];
            
                for ($i = 1; $i <= 12; $i++) {
                    $incomeArr[$year][$i] = 0;
                }
            
                if (isset($revenueTotalArray[$year])) {
                    foreach ($revenueTotalArray[$year] as $month => $values) {
                        $incomeArr[$year][$month] = array_sum($values);
                    }
                }
            }

            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTotalArray[$invoice->year][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceArr = [];
            $incomesum = [];

            foreach ($yearList as $year) {
                $invoiceArr[$year] = [];
            
                for ($i = 1; $i <= 12; $i++) {
                    $invoiceArr[$year][$i] = 0;
                }
            
                if (isset($invoiceTotalArray[$year])) {
                    foreach ($invoiceTotalArray[$year] as $month => $values) {
                        $invoiceArr[$year][$month] = array_sum($values);
                    }
                }
            }

            $invoicesum = Utility::totalSum($invoiceArr, $request , $yearList);

            $incomesum = Utility::totalSum($incomeArr, $request , $yearList);

            $chartIncomeArr = Utility::totalData($invoiceArr, $incomeArr, $request , $yearList);



            $profit = [];

                if (count($chartIncomeArr) === count($chartExpenseArr) && count($chartIncomeArr[0]) === count($chartExpenseArr[0])) {
                    foreach ($chartIncomeArr as $i => $values1) {
                        foreach ($values1 as $j => $value1) {
                            $profit[$i][$j] = $value1 - $chartExpenseArr[$i][$j];
                        }
                    }
                } 


            $data['paymentExpenseTotal'] = $expensesum;
            $data['billExpenseTotal'] = $billsum;
            $data['revenueIncomeTotal'] = $incomesum;
            $data['invoiceIncomeTotal'] = $invoicesum;
            $data['profit'] = $profit;
            $data['account'] = $account;
            $data['vender'] = $vender;
            $data['customer'] = $customer;
            $data['category'] = $category;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;

            return view('report.income_vs_expense_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function taxSummary(Request $request)
    {

        if (\Auth::user()->can('tax report')) {
            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList']  = $this->yearList();
            $data['taxList']   = $taxList = Tax::where('created_by', \Auth::user()->creatorId())->get();

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }

            $data['currentYear'] = $year;

            $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->where('product_services.created_by', '=', \Auth::user()->creatorId())->get();

            $incomeTaxesData = [];
            foreach ($invoiceProducts as $invoiceProduct) {
                $incomeTax   = [];
                $incomeTaxes = Utility::tax($invoiceProduct->tax);

                foreach ($incomeTaxes as $taxe) {
                    $taxDataPrice           = Utility::taxRate(!empty($taxe) ? ($taxe->rate) : 0, $invoiceProduct->price, $invoiceProduct->quantity);
                    $incomeTax[!empty($taxe) ? ($taxe->name) : ''] = $taxDataPrice;
                }
                $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
            }

            $income = [];
            foreach ($incomeTaxesData as $month => $incomeTaxx) {
                $incomeTaxRecord = [];
                foreach ($incomeTaxx as $k => $record) {
                    foreach ($record as $incomeTaxName => $incomeTaxAmount) {
                        if (array_key_exists($incomeTaxName, $incomeTaxRecord)) {
                            $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                        } else {
                            $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                        }
                    }
                    $income['data'][$month] = $incomeTaxRecord;
                }
            }

            foreach ($income as $incomeMonth => $incomeTaxData) {
                $incomeData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                }
            }

            $incomes = [];
            if (isset($incomeData) && !empty($incomeData)) {
                foreach ($taxList as $taxArr) {
                    foreach ($incomeData as $month => $tax) {
                        if ($tax != 0) {
                            if (isset($tax[$taxArr->name])) {
                                $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                            } else {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        } else {
                            $incomes[$taxArr->name][$month] = 0;
                        }
                    }
                }
            }


            $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->where('product_services.created_by', '=', \Auth::user()->creatorId())->get();

            $expenseTaxesData = [];
            foreach ($billProducts as $billProduct) {
                $billTax   = [];
                $billTaxes = Utility::tax($billProduct->tax);
                foreach ($billTaxes as $taxe) {
                    $taxDataPrice         = Utility::taxRate(!empty($taxe) ? ($taxe->rate) : 0, $billProduct->price, $billProduct->quantity);
                    $billTax[!empty($taxe) ? ($taxe->name) : ''] = $taxDataPrice;
                }
                $expenseTaxesData[$billProduct->month][] = $billTax;
            }

            $bill = [];
            foreach ($expenseTaxesData as $month => $billTaxx) {
                $billTaxRecord = [];
                foreach ($billTaxx as $k => $record) {
                    foreach ($record as $billTaxName => $billTaxAmount) {
                        if (array_key_exists($billTaxName, $billTaxRecord)) {
                            $billTaxRecord[$billTaxName] += $billTaxAmount;
                        } else {
                            $billTaxRecord[$billTaxName] = $billTaxAmount;
                        }
                    }
                    $bill['data'][$month] = $billTaxRecord;
                }
            }

            foreach ($bill as $billMonth => $billTaxData) {
                $billData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                }
            }
            $expenses = [];
            if (isset($billData) && !empty($billData)) {

                foreach ($taxList as $taxArr) {
                    foreach ($billData as $month => $tax) {
                        if ($tax != 0) {
                            if (isset($tax[$taxArr->name])) {
                                $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                            } else {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        } else {
                            $expenses[$taxArr->name][$month] = 0;
                        }
                    }
                }
            }

            $data['expenses'] = $expenses;
            $data['incomes']  = $incomes;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;

            return view('report.tax_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function profitLossSummary(Request $request)
    // {

    //     if (\Auth::user()->can('loss & profit report')) {
    //         $data['month']     = [
    //             'Jan-Mar',
    //             'Apr-Jun',
    //             'Jul-Sep',
    //             'Oct-Dec',
    //             'Total',
    //         ];
    //         $data['monthList'] = $month = $this->yearMonth();
    //         $data['yearList']  = $this->yearList();

    //         if (isset($request->year)) {
    //             $year = $request->year;
    //         } else {
    //             $year = date('Y');
    //         }
    //         $data['currentYear'] = $year;

    //         // -------------------------------REVENUE INCOME-------------------------------------------------

    //         $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
    //         $incomes->where('created_by', '=', \Auth::user()->creatorId());
    //         $incomes->whereRAW('YEAR(date) =?', [$year]);
    //         $incomes->groupBy('month', 'year', 'category_id');
    //         $incomes        = $incomes->get();
    //         $tmpIncomeArray = [];
    //         foreach ($incomes as $income) {
    //             $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
    //         }

    //         $incomeCatAmount_1  = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
    //         $revenueIncomeArray = array();
    //         foreach ($tmpIncomeArray as $cat_id => $record) {

    //             $tmp             = [];
    //             $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
    //             $sumData         = [];
    //             for ($i = 1; $i <= 12; $i++) {
    //                 $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
    //             }

    //             $month_1 = array_slice($sumData, 0, 3);
    //             $month_2 = array_slice($sumData, 3, 3);
    //             $month_3 = array_slice($sumData, 6, 3);
    //             $month_4 = array_slice($sumData, 9, 3);


    //             $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
    //             $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
    //             $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
    //             $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
    //             $incomeData[__('Total')]   = array_sum(
    //                 array(
    //                     $sum_1,
    //                     $sum_2,
    //                     $sum_3,
    //                     $sum_4,
    //                 )
    //             );

    //             $incomeCatAmount_1 += $sum_1;
    //             $incomeCatAmount_2 += $sum_2;
    //             $incomeCatAmount_3 += $sum_3;
    //             $incomeCatAmount_4 += $sum_4;

    //             $data['month'] = array_keys($incomeData);
    //             $tmp['amount'] = array_values($incomeData);

    //             $revenueIncomeArray[] = $tmp;
    //         }

    //         $data['incomeCatAmount'] = $incomeCatAmount = [
    //             $incomeCatAmount_1,
    //             $incomeCatAmount_2,
    //             $incomeCatAmount_3,
    //             $incomeCatAmount_4,
    //             array_sum(
    //                 array(
    //                     $incomeCatAmount_1,
    //                     $incomeCatAmount_2,
    //                     $incomeCatAmount_3,
    //                     $incomeCatAmount_4,
    //                 )
    //             ),
    //         ];

    //         $data['revenueIncomeArray'] = $revenueIncomeArray;

    //         //-----------------------INVOICE INCOME---------------------------------------------

    //         $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
    //         $invoices->whereRAW('YEAR(send_date) =?', [$year]);
    //         if (!empty($request->customer)) {
    //             $invoices->where('customer_id', '=', $request->customer);
    //         }
    //         $invoices        = $invoices->get();
    //         $invoiceTmpArray = [];
    //         foreach ($invoices as $invoice) {
    //             $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
    //         }

    //         $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
    //         $invoiceIncomeArray = array();
    //         foreach ($invoiceTmpArray as $cat_id => $record) {

    //             $invoiceTmp             = [];
    //             $category = ProductServiceCategory::find($cat_id);
    //             $invoiceTmp['category'] = $category ? $category->name : '';
    //             $invoiceSumData         = [];
    //             for ($i = 1; $i <= 12; $i++) {
    //                 $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
    //             }

    //             $month_1                          = array_slice($invoiceSumData, 0, 3);
    //             $month_2                          = array_slice($invoiceSumData, 3, 3);
    //             $month_3                          = array_slice($invoiceSumData, 6, 3);
    //             $month_4                          = array_slice($invoiceSumData, 9, 3);
    //             $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
    //             $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
    //             $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
    //             $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
    //             $invoiceIncomeData[__('Total')]   = array_sum(
    //                 array(
    //                     $sum_1,
    //                     $sum_2,
    //                     $sum_3,
    //                     $sum_4,
    //                 )
    //             );
    //             $invoiceCatAmount_1               += $sum_1;
    //             $invoiceCatAmount_2               += $sum_2;
    //             $invoiceCatAmount_3               += $sum_3;
    //             $invoiceCatAmount_4               += $sum_4;

    //             $invoiceTmp['amount'] = array_values($invoiceIncomeData);

    //             $invoiceIncomeArray[] = $invoiceTmp;
    //         }

    //         $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
    //             $invoiceCatAmount_1,
    //             $invoiceCatAmount_2,
    //             $invoiceCatAmount_3,
    //             $invoiceCatAmount_4,
    //             array_sum(
    //                 array(
    //                     $invoiceCatAmount_1,
    //                     $invoiceCatAmount_2,
    //                     $invoiceCatAmount_3,
    //                     $invoiceCatAmount_4,
    //                 )
    //             ),
    //         ];


    //         $data['invoiceIncomeArray'] = $invoiceIncomeArray;

    //         $data['totalIncome'] = $totalIncome = array_map(
    //             function () {
    //                 return array_sum(func_get_args());
    //             },
    //             $invoiceIncomeCatAmount,
    //             $incomeCatAmount
    //         );

    //         //---------------------------------PAYMENT EXPENSE-----------------------------------

    //         $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
    //         $expenses->where('created_by', '=', \Auth::user()->creatorId());
    //         $expenses->whereRAW('YEAR(date) =?', [$year]);
    //         $expenses->groupBy('month', 'year', 'category_id');
    //         $expenses = $expenses->get();

    //         $tmpExpenseArray = [];
    //         foreach ($expenses as $expense) {
    //             $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
    //         }

    //         $expenseArray       = [];
    //         $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
    //         foreach ($tmpExpenseArray as $cat_id => $record) {
    //             $tmp             = [];
    //             $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
    //             $expenseSumData  = [];
    //             for ($i = 1; $i <= 12; $i++) {
    //                 $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
    //             }

    //             $month_1 = array_slice($expenseSumData, 0, 3);
    //             $month_2 = array_slice($expenseSumData, 3, 3);
    //             $month_3 = array_slice($expenseSumData, 6, 3);
    //             $month_4 = array_slice($expenseSumData, 9, 3);

    //             $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
    //             $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
    //             $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
    //             $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
    //             $expenseData[__('Total')]   = array_sum(
    //                 array(
    //                     $sum_1,
    //                     $sum_2,
    //                     $sum_3,
    //                     $sum_4,
    //                 )
    //             );

    //             $expenseCatAmount_1 += $sum_1;
    //             $expenseCatAmount_2 += $sum_2;
    //             $expenseCatAmount_3 += $sum_3;
    //             $expenseCatAmount_4 += $sum_4;

    //             $data['month'] = array_keys($expenseData);
    //             $tmp['amount'] = array_values($expenseData);

    //             $expenseArray[] = $tmp;
    //         }

    //         $data['expenseCatAmount'] = $expenseCatAmount = [
    //             $expenseCatAmount_1,
    //             $expenseCatAmount_2,
    //             $expenseCatAmount_3,
    //             $expenseCatAmount_4,
    //             array_sum(
    //                 array(
    //                     $expenseCatAmount_1,
    //                     $expenseCatAmount_2,
    //                     $expenseCatAmount_3,
    //                     $expenseCatAmount_4,
    //                 )
    //             ),
    //         ];
    //         $data['expenseArray']     = $expenseArray;

    //         //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------

    //         $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
    //         $bills->whereRAW('YEAR(send_date) =?', [$year]);
    //         if (!empty($request->customer)) {
    //             $bills->where('vender_id', '=', $request->vender);
    //         }
    //         $bills        = $bills->get();
    //         $billTmpArray = [];
    //         foreach ($bills as $bill) {
    //             $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
    //         }

    //         $billExpenseArray       = [];
    //         $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
    //         foreach ($billTmpArray as $cat_id => $record) {
    //             $billTmp             = [];
    //             $billTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
    //             $billExpensSumData   = [];
    //             for ($i = 1; $i <= 12; $i++) {
    //                 $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
    //             }

    //             $month_1 = array_slice($billExpensSumData, 0, 3);
    //             $month_2 = array_slice($billExpensSumData, 3, 3);
    //             $month_3 = array_slice($billExpensSumData, 6, 3);
    //             $month_4 = array_slice($billExpensSumData, 9, 3);

    //             $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
    //             $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
    //             $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
    //             $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
    //             $billExpenseData[__('Total')]   = array_sum(
    //                 array(
    //                     $sum_1,
    //                     $sum_2,
    //                     $sum_3,
    //                     $sum_4,
    //                 )
    //             );

    //             $billExpenseCatAmount_1 += $sum_1;
    //             $billExpenseCatAmount_2 += $sum_2;
    //             $billExpenseCatAmount_3 += $sum_3;
    //             $billExpenseCatAmount_4 += $sum_4;

    //             $data['month']     = array_keys($billExpenseData);
    //             $billTmp['amount'] = array_values($billExpenseData);

    //             $billExpenseArray[] = $billTmp;
    //         }

    //         $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
    //             $billExpenseCatAmount_1,
    //             $billExpenseCatAmount_2,
    //             $billExpenseCatAmount_3,
    //             $billExpenseCatAmount_4,
    //             array_sum(
    //                 array(
    //                     $billExpenseCatAmount_1,
    //                     $billExpenseCatAmount_2,
    //                     $billExpenseCatAmount_3,
    //                     $billExpenseCatAmount_4,
    //                 )
    //             ),
    //         ];

    //         $data['billExpenseArray'] = $billExpenseArray;


    //         $data['totalExpense'] = $totalExpense = array_map(
    //             function () {
    //                 return array_sum(func_get_args());
    //             },
    //             $billExpenseCatAmount,
    //             $expenseCatAmount
    //         );


    //         foreach ($totalIncome as $k => $income) {
    //             $netProfit[] = $income - $totalExpense[$k];
    //         }
    //         $data['netProfitArray'] = $netProfit;

    //         $filter['startDateRange'] = 'Jan-' . $year;
    //         $filter['endDateRange']   = 'Dec-' . $year;

    //         return view('report.profit_loss_summary', compact('filter'), $data);
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function profitLoss(Request $request, $view = '', $collapseView = 'expand')
    {
        if (\Auth::user()->can('income vs expense report')) {
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-01-01');
                $end = date('Y-m-d', strtotime('+1 day'));
            }
            $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Income', 'Expenses', 'Costs of Goods Sold'])->get();
            $subTypeArray = [];
            $totalAccounts = [];
            foreach ($types as $type) {

                $parentAccounts = ChartOfAccount::where('type', $type->id)->where('created_by', \Auth::user()->creatorId())->get();

                $totalParentAccountArray = [];
                if ($parentAccounts->isNotEmpty()) {
                    foreach ($parentAccounts as $parentAccount) {
                        $totalArray = [];
                        $parentAccountArray = [];
                        $parentAccountArrayTotal = [];

                        $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $parentAccs->where('chart_of_accounts.type', $type->id);
                        $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                        $parentAccs->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $parentAccs->where('transaction_lines.date', '>=', $start);
                        $parentAccs->where('transaction_lines.date', '<=', $end);
                        $parentAccs->groupBy('account_id');
                        $parentAccs = $parentAccs->get()->toArray();

                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                        $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();

                        if ($parentAccs == [] && $accounts != []) {

                            $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                            $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                            $parentAccs = $parentAccs->get()->toArray();
                        }
                        if ($parentAccs != [] && $accounts == []) {

                            $parentAccs = [];
                        }

                        $parenttotalBalance = 0;
                        $parentcreditTotal = 0;
                        $parenntdebitTotal = 0;
                        $parenttotalAmount = 0;

                        foreach ($parentAccs as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            $data = [
                                'account_id' => $account['id'],
                                'account_code' => $account['code'],
                                'account_name' => $account['name'],
                                'account' => 'parent',
                                'totalCredit' => 0,
                                'totalDebit' => 0,
                                'netAmount' => $Balance,
                            ];

                            $parentAccountArray[] = $data;
                            $parentcreditTotal += $data['totalCredit'];
                            $parenntdebitTotal += $data['totalDebit'];
                            $parenttotalAmount += $data['netAmount'];
                        }

                        foreach ($accounts as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            if ($Balance != 0) {
                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'subAccount',
                                    'totalCredit' => 0,
                                    'totalDebit' => 0,
                                    'netAmount' => $Balance,
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                                $parenttotalAmount += $data['netAmount'];
                            }
                        }

                        if (!empty($parentAccountArray)) {
                            $dataTotal = [
                                'account_id' => $parentAccount->account,
                                'account_code' => '',
                                'account' => 'parentTotal',
                                'account_name' => 'Total ' . $parentAccount->name,
                                'totalCredit' => $parentcreditTotal,
                                'totalDebit' => $parenntdebitTotal,
                                'netAmount' => $parenttotalAmount,
                            ];

                            $parentAccountArrayTotal[] = $dataTotal;
                            $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                            $totalParentAccountArray[] = $totalArray;
                        }

                    }

                }

                if ($totalParentAccountArray != []) {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_account_parents.account');
                    $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                } else {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                }

                $totalBalance = 0;
                $creditTotal = 0;
                $debitTotal = 0;
                $totalAmount = 0;
                $accountArray = [];
                foreach ($accounts as $account) {
                    $Balance = $account['totalCredit'] - $account['totalDebit'];
                    $totalBalance += $Balance;
                    if ($Balance != 0) {
                        $data['account_id'] = $account['id'];
                        $data['account_code'] = $account['code'];
                        $data['account_name'] = $account['name'];
                        $data['account'] = '';
                        $data['totalCredit'] = 0;
                        $data['totalDebit'] = 0;
                        $data['netAmount'] = $Balance;
                        $accountArray[][] = $data;
                        $creditTotal += $data['totalCredit'];
                        $debitTotal += $data['totalDebit'];
                        $totalAmount += $data['netAmount'];
                    }
                }

                $totalAccountArray = [];

                if ($accountArray != []) {
                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $type->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;
                    $dataTotal['netAmount'] = $totalAmount;
                    $accountArray[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArray);

                } elseif ($totalParentAccountArray != []) {

                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $type->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;
                    $netAmount = 0;
                    foreach ($totalParentAccountArray as $innerArray) {
                        $lastElement = end($innerArray);

                        $netAmount += $lastElement['netAmount'];
                    }
                    $dataTotal['netAmount'] = $netAmount;
                    $accountArrayTotal[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArrayTotal);
                }
                if ($totalAccountArray != []) {
                    $subTypeData['Type'] = ($totalAccountArray != []) ? $type->name : '';
                    $subTypeData['account'] = $totalAccountArray;
                    $subTypeArray[] = ($subTypeData['account'] != []) ? $subTypeData : [];
                }
                $totalAccounts = $subTypeArray;
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            if ($request->view == 'horizontal' || $view == 'horizontal') {
                return view('report.profit_loss_horizontal', compact('filter', 'totalAccounts', 'collapseView'));
            } elseif ($view == '' || $view == 'vertical') {
                return view('report.profit_loss', compact('filter', 'totalAccounts', 'collapseView'));
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function monthlyCashflow(Request $request)
    {
        if (\Auth::user()->can('loss & profit report')) {

            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList'] = $this->yearList();

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // -------------------------------REVENUE INCOME-------------------------------------------------

            // ------------------------------REVENUE INCOME-----------------------------------
            $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')
                ->leftjoin('product_service_categories', 'revenues.category_id', '=', 'product_service_categories.id')->where('product_service_categories.type', '=', 1);
            $incomes->where('revenues.created_by', '=', \Auth::user()->creatorId());
            $incomes->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $incomes->where('category_id', '=', $request->category);
                $cat = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }

            if (!empty($request->customer)) {
                $incomes->where('customer_id', '=', $request->customer);
                $cust = Customer::find($request->customer);
                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $incomes->groupBy('month', 'year', 'category_id');
            $incomes = $incomes->get();

            $tmpArray = [];
            foreach ($incomes as $income) {
                $tmpArray[$income->category_id][$income->month] = $income->amount;
            }
            $array = [];
            foreach ($tmpArray as $cat_id => $record) {
                $tmp = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $tmp['data'] = [];
                for ($i = 1; $i <= 12; $i++) {
                    $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                }
                $array[] = $tmp;
            }

            $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $incomesData->where('revenues.created_by', '=', \Auth::user()->creatorId());
            $incomesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $incomesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->customer)) {
                $incomesData->where('customer_id', '=', $request->customer);
            }
            $incomesData->groupBy('month', 'year');
            $incomesData = $incomesData->get();
            $incomeArr = [];
            foreach ($incomesData as $k => $incomeData) {
                $incomeArr[$incomeData->month] = $incomeData->amount;
            }
            for ($i = 1; $i <= 12; $i++) {
                $incomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
            }

            //---------------------------INVOICE INCOME-----------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')
                ->where('created_by', \Auth::user()->creatorId())
                ->where('status', '!=', 0);

            $invoices->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }

            if (!empty($request->category)) {
                $invoices->where('category_id', '=', $request->category);
            }

            $invoices = $invoices->get();
            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceArray = [];
            foreach ($invoiceTmpArray as $cat_id => $record) {

                $invoice = [];
                $productCtegory = ProductServiceCategory::where('id', '=', $cat_id)->first();
                $invoice['category'] = !empty($productCtegory) ? $productCtegory->name : '';
                $invoice['data'] = [];
                for ($i = 1; $i <= 12; $i++) {

                    $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $invoiceArray[] = $invoice;
            }

            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            for ($i = 1; $i <= 12; $i++) {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $chartIncomeArr = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $incomeTotal, $invoiceTotal
            );

            $data['chartIncomeArr'] = $chartIncomeArr;
            $data['incomeArr'] = $array;
            $data['invoiceArray'] = $invoiceArray;

            //   -----------------------------------------PAYMENT EXPENSE ------------------------------------------------------------
            $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('product_service_categories', 'payments.category_id', '=', 'product_service_categories.id')->where('product_service_categories.type', '=', 2);
            $expenses->where('payments.created_by', '=', \Auth::user()->creatorId());
            $expenses->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expenses->where('category_id', '=', $request->category);
                $cat = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }
            if (!empty($request->vender)) {
                $expenses->where('vender_id', '=', $request->vender);

                $vend = Vender::find($request->vender);
                $filter['vender'] = !empty($vend) ? $vend->name : '';
            }

            $expenses->groupBy('month', 'year', 'category_id');
            $expenses = $expenses->get();
            $tmpArray = [];
            foreach ($expenses as $expense) {
                $tmpArray[$expense->category_id][$expense->month] = $expense->amount;
            }
            $array = [];
            foreach ($tmpArray as $cat_id => $record) {
                $tmp = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $tmp['data'] = [];
                for ($i = 1; $i <= 12; $i++) {
                    $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                }
                $array[] = $tmp;
            }
            $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $expensesData->where('payments.created_by', '=', \Auth::user()->creatorId());
            $expensesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expensesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->vender)) {
                $expensesData->where('vender_id', '=', $request->vender);
            }
            $expensesData->groupBy('month', 'year');
            $expensesData = $expensesData->get();

            $expenseArr = [];
            foreach ($expensesData as $k => $expenseData) {
                $expenseArr[$expenseData->month] = $expenseData->amount;
            }
            for ($i = 1; $i <= 12; $i++) {
                $expenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
            }

            //     ------------------------------------BILL EXPENSE----------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->vender)) {
                $bills->where('vender_id', '=', $request->vender);
            }

            if (!empty($request->category)) {
                $bills->where('category_id', '=', $request->category);
            }
            $bills = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }

            $billArray = [];
            foreach ($billTmpArray as $cat_id => $record) {

                $bill = [];
                $productCategory = ProductServiceCategory::where('id', '=', $cat_id)->first();
                $bill['category'] = !empty($productCategory) ? $productCategory->name : '';
                $bill['data'] = [];
                for ($i = 1; $i <= 12; $i++) {

                    $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $billArray[] = $bill;
            }

            $billTotalArray = [];
            foreach ($bills as $bill) {
                $billTotalArray[$bill->month][] = $bill->getTotal();
            }
            for ($i = 1; $i <= 12; $i++) {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            $chartExpenseArr = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $expenseTotal, $billTotal
            );

            $netProfit = [];
            $keys = array_keys($chartIncomeArr + $chartExpenseArr);
            foreach ($keys as $v) {
                $netProfit[$v] = (empty($chartIncomeArr[$v]) ? 0 : $chartIncomeArr[$v]) - (empty($chartExpenseArr[$v]) ? 0 : $chartExpenseArr[$v]);
            }

            $data['chartExpenseArr'] = $chartExpenseArr;
            $data['expenseArr'] = $array;
            $data['billArray'] = $billArray;

            $data['netProfitArray'] = $netProfit;
            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;

            return view('report.monthly_cashflow', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function quarterlyCashflow(Request $request)
    {

        if (\Auth::user()->can('loss & profit report')) {
            $data['month'] = [
                'Jan-Mar',
                'Apr-Jun',
                'Jul-Sep',
                'Oct-Dec',
                'Total',
            ];
            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList'] = $this->yearList();

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // -------------------------------REVENUE INCOME-------------------------------------------------

            $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
            $incomes->where('created_by', '=', \Auth::user()->creatorId());
            $incomes->whereRAW('YEAR(date) =?', [$year]);
            $incomes->groupBy('month', 'year', 'category_id');
            $incomes = $incomes->get();
            $tmpIncomeArray = [];
            foreach ($incomes as $income) {
                $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
            }

            $incomeCatAmount_1 = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
            $revenueIncomeArray = array();
            foreach ($tmpIncomeArray as $cat_id => $record) {

                $tmp = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $sumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                }

                $month_1 = array_slice($sumData, 0, 3);
                $month_2 = array_slice($sumData, 3, 3);
                $month_3 = array_slice($sumData, 6, 3);
                $month_4 = array_slice($sumData, 9, 3);

                $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $incomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $incomeCatAmount_1 += $sum_1;
                $incomeCatAmount_2 += $sum_2;
                $incomeCatAmount_3 += $sum_3;
                $incomeCatAmount_4 += $sum_4;

                $data['month'] = array_keys($incomeData);
                $tmp['amount'] = array_values($incomeData);

                $revenueIncomeArray[] = $tmp;

            }

            $data['incomeCatAmount'] = $incomeCatAmount = [
                $incomeCatAmount_1,
                $incomeCatAmount_2,
                $incomeCatAmount_3,
                $incomeCatAmount_4,
                array_sum(
                    array(
                        $incomeCatAmount_1,
                        $incomeCatAmount_2,
                        $incomeCatAmount_3,
                        $incomeCatAmount_4,
                    )
                ),
            ];

            $data['revenueIncomeArray'] = $revenueIncomeArray;

            //-----------------------INVOICE INCOME---------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $invoices->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }
            $invoices = $invoices->get();

            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getDue();
            }

            $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;

            $invoiceIncomeArray = array();
            foreach ($invoiceTmpArray as $cat_id => $record) {

                $invoiceTmp = [];
                $invoiceTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $invoiceSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                }

                $month_1 = array_slice($invoiceSumData, 0, 3);
                $month_2 = array_slice($invoiceSumData, 3, 3);
                $month_3 = array_slice($invoiceSumData, 6, 3);
                $month_4 = array_slice($invoiceSumData, 9, 3);
                $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $invoiceIncomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $invoiceCatAmount_1 += $sum_1;
                $invoiceCatAmount_2 += $sum_2;
                $invoiceCatAmount_3 += $sum_3;
                $invoiceCatAmount_4 += $sum_4;

                $invoiceTmp['amount'] = array_values($invoiceIncomeData);

                $invoiceIncomeArray[] = $invoiceTmp;

            }

            $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
                $invoiceCatAmount_1,
                $invoiceCatAmount_2,
                $invoiceCatAmount_3,
                $invoiceCatAmount_4,
                array_sum(
                    array(
                        $invoiceCatAmount_1,
                        $invoiceCatAmount_2,
                        $invoiceCatAmount_3,
                        $invoiceCatAmount_4,
                    )
                ),
            ];

            $data['invoiceIncomeArray'] = $invoiceIncomeArray;

            $data['totalIncome'] = $totalIncome = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $invoiceIncomeCatAmount, $incomeCatAmount
            );

            //---------------------------------PAYMENT EXPENSE-----------------------------------

            $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
            $expenses->where('created_by', '=', \Auth::user()->creatorId());
            $expenses->whereRAW('YEAR(date) =?', [$year]);
            $expenses->groupBy('month', 'year', 'category_id');
            $expenses = $expenses->get();

            $tmpExpenseArray = [];
            foreach ($expenses as $expense) {
                $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
            }

            $expenseArray = [];
            $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
            foreach ($tmpExpenseArray as $cat_id => $record) {
                $tmp = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $expenseSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;

                }

                $month_1 = array_slice($expenseSumData, 0, 3);
                $month_2 = array_slice($expenseSumData, 3, 3);
                $month_3 = array_slice($expenseSumData, 6, 3);
                $month_4 = array_slice($expenseSumData, 9, 3);

                $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $expenseData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $expenseCatAmount_1 += $sum_1;
                $expenseCatAmount_2 += $sum_2;
                $expenseCatAmount_3 += $sum_3;
                $expenseCatAmount_4 += $sum_4;

                $data['month'] = array_keys($expenseData);
                $tmp['amount'] = array_values($expenseData);

                $expenseArray[] = $tmp;

            }

            $data['expenseCatAmount'] = $expenseCatAmount = [
                $expenseCatAmount_1,
                $expenseCatAmount_2,
                $expenseCatAmount_3,
                $expenseCatAmount_4,
                array_sum(
                    array(
                        $expenseCatAmount_1,
                        $expenseCatAmount_2,
                        $expenseCatAmount_3,
                        $expenseCatAmount_4,
                    )
                ),
            ];
            $data['expenseArray'] = $expenseArray;

            //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $bills->where('vender_id', '=', $request->vender);
            }
            $bills = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }

            $billExpenseArray = [];
            $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
            foreach ($billTmpArray as $cat_id => $record) {
                $billTmp = [];
                $billTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $billExpensSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }

                $month_1 = array_slice($billExpensSumData, 0, 3);
                $month_2 = array_slice($billExpensSumData, 3, 3);
                $month_3 = array_slice($billExpensSumData, 6, 3);
                $month_4 = array_slice($billExpensSumData, 9, 3);

                $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $billExpenseData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $billExpenseCatAmount_1 += $sum_1;
                $billExpenseCatAmount_2 += $sum_2;
                $billExpenseCatAmount_3 += $sum_3;
                $billExpenseCatAmount_4 += $sum_4;

                $data['month'] = array_keys($billExpenseData);
                $billTmp['amount'] = array_values($billExpenseData);

                $billExpenseArray[] = $billTmp;

            }

            $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
                $billExpenseCatAmount_1,
                $billExpenseCatAmount_2,
                $billExpenseCatAmount_3,
                $billExpenseCatAmount_4,
                array_sum(
                    array(
                        $billExpenseCatAmount_1,
                        $billExpenseCatAmount_2,
                        $billExpenseCatAmount_3,
                        $billExpenseCatAmount_4,
                    )
                ),
            ];

            $data['billExpenseArray'] = $billExpenseArray;

            $data['totalExpense'] = $totalExpense = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $billExpenseCatAmount, $expenseCatAmount
            );

            foreach ($totalIncome as $k => $income) {
                $netProfit[] = $income - $totalExpense[$k];
            }
            $data['netProfitArray'] = $netProfit;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;

            return view('report.quarterly_cashflow', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    public function profitLossExport(Request $request)
    {

        if (\Auth::user()->can('income vs expense report')) {

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-01-01');
                $end = date('Y-m-d', strtotime('+1 day'));
            }

            $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Income', 'Expenses', 'Costs of Goods Sold'])->get();
            $subTypeArray = [];
            $totalAccounts = [];
            foreach ($types as $type) {

                $parentAccounts = ChartOfAccountParent::where('type', $type->id)->where('created_by', \Auth::user()->creatorId())->get();

                $totalParentAccountArray = [];
                if ($parentAccounts->isNotEmpty()) {
                    foreach ($parentAccounts as $parentAccount) {
                        $totalArray = [];
                        $parentAccountArray = [];
                        $parentAccountArrayTotal = [];

                        $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $parentAccs->where('chart_of_accounts.type', $type->id);
                        $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                        $parentAccs->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $parentAccs->where('transaction_lines.date', '>=', $start);
                        $parentAccs->where('transaction_lines.date', '<=', $end);
                        $parentAccs->groupBy('account_id');
                        $parentAccs = $parentAccs->get()->toArray();

                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                        $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();

                        if ($parentAccs == [] && $accounts != []) {

                            $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                            $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                            $parentAccs = $parentAccs->get()->toArray();
                        }
                        if ($parentAccs != [] && $accounts == []) {

                            $parentAccs = [];
                        }

                        $parenttotalBalance = 0;
                        $parentcreditTotal = 0;
                        $parenntdebitTotal = 0;
                        $parenttotalAmount = 0;

                        foreach ($parentAccs as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            $data = [
                                'account_id' => $account['id'],
                                'account_code' => $account['code'],
                                'account_name' => $account['name'],
                                'account' => 'parent',
                                'totalCredit' => 0,
                                'totalDebit' => 0,
                                'netAmount' => $Balance,
                            ];

                            $parentAccountArray[] = $data;
                            $parentcreditTotal += $data['totalCredit'];
                            $parenntdebitTotal += $data['totalDebit'];
                            $parenttotalAmount += $data['netAmount'];
                        }

                        foreach ($accounts as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            if ($Balance != 0) {
                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'subAccount',
                                    'totalCredit' => 0,
                                    'totalDebit' => 0,
                                    'netAmount' => $Balance,
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                                $parenttotalAmount += $data['netAmount'];
                            }
                        }

                        if (!empty($parentAccountArray)) {
                            $dataTotal = [
                                'account_id' => $parentAccount->account,
                                'account_code' => '',
                                'account' => 'parentTotal',
                                'account_name' => 'Total ' . $parentAccount->name,
                                'totalCredit' => $parentcreditTotal,
                                'totalDebit' => $parenntdebitTotal,
                                'netAmount' => $parenttotalAmount,
                            ];

                            $parentAccountArrayTotal[] = $dataTotal;
                            $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                            $totalParentAccountArray[] = $totalArray;
                        }

                    }

                }

                if ($totalParentAccountArray != []) {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_account_parents.account');
                    $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                } else {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                }

                $totalBalance = 0;
                $creditTotal = 0;
                $debitTotal = 0;
                $totalAmount = 0;
                $accountArray = [];
                foreach ($accounts as $account) {
                    $Balance = $account['totalCredit'] - $account['totalDebit'];
                    $totalBalance += $Balance;
                    if ($Balance != 0) {
                        $data['account_id'] = $account['id'];
                        $data['account_code'] = $account['code'];
                        $data['account_name'] = $account['name'];
                        $data['account'] = '';
                        $data['totalCredit'] = 0;
                        $data['totalDebit'] = 0;
                        $data['netAmount'] = $Balance;
                        $accountArray[][] = $data;
                        $creditTotal += $data['totalCredit'];
                        $debitTotal += $data['totalDebit'];
                        $totalAmount += $data['netAmount'];
                    }
                }

                $totalAccountArray = [];

                if ($accountArray != []) {
                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $type->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;
                    $dataTotal['netAmount'] = $totalAmount;
                    $accountArray[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArray);

                } elseif ($totalParentAccountArray != []) {

                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $type->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;
                    $netAmount = 0;
                    foreach ($totalParentAccountArray as $innerArray) {
                        $lastElement = end($innerArray);

                        $netAmount += $lastElement['netAmount'];
                    }
                    $dataTotal['netAmount'] = $netAmount;
                    $accountArrayTotal[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArrayTotal);
                }
                if ($totalAccountArray != []) {
                    $subTypeData['Type'] = ($totalAccountArray != []) ? $type->name : '';
                    $subTypeData['account'] = $totalAccountArray;
                    $subTypeArray[] = ($subTypeData['account'] != []) ? $subTypeData : [];
                }
                $totalAccounts = $subTypeArray;
            }
            $companyName = User::where('id', \Auth::user()->creatorId())->first();
            $companyName = $companyName->name;

            $name = 'profit & loss_' . date('Y-m-d i:h:s');
            $data = Excel::download(new ProfitLossExport($totalAccounts, $start, $end, $companyName), $name . '.xlsx');
            ob_end_clean();

            return $data;
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach (range($ending_year, $starting_year) as $year) {
            $years[$year] = $year;
        }

        return $years;
    }

    public static function period()
    {
        return [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'half-yearly' => 'Half Yearly',
            'yearly' => 'Yearly',
        ];
    }

    public function invoiceSummary(Request $request)
    {

        if (\Auth::user()->can('invoice report')) {
            $filter['customer'] = __('All');
            $filter['status']   = __('All');


            $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'customer_id');
            $customer->prepend('Select Customer', '');
            $status = Status::getAllAsArray();

            $invoices = Invoice::with('payments')->selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');

            if ($request->status != '') {
                $invoices->where('status', $request->status);

                $filter['status'] = Status::getNameById($request->status);
            } else {
                $invoices->where('status', '!=', 0);
            }

            $invoices->where('created_by', '=', \Auth::user()->creatorId());

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if (!empty($request->customer)) {
                $invoices->where('customer_id', $request->customer);
                $cust = Customer::find($request->customer);

                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }


            $invoices = $invoices->get();


            $totalInvoice      = 0;
            $totalDueInvoice   = 0;
            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $totalInvoice = $invoices->sum(function ($invoice) {
                    return $invoice->getTotal();
                });

                $totalDueInvoice = $invoices->sum(function ($invoice) {
                    return $invoice->getDue();
                });

                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $totalPaidInvoice = $totalInvoice - $totalDueInvoice;

            for ($i = 1; $i <= 12; $i++) {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            return view('report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function billSummary(Request $request)
    {
        if (\Auth::user()->can('bill report')) {

            $filter['vender'] = __('All');
            $filter['status'] = __('All');


            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'vender_id');
            $vender->prepend('Select Vendor', '');
            $status = $status = Status::getAllAsArray();

            $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year');

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $bills->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if (!empty($request->vender)) {
                $bills->where('vender_id', $request->vender);
                $vend = Vender::find($request->vender);

                $filter['vender'] = !empty($vend) ? $vend->name : '';
            }

            if ($request->status != '') {
                $bills->where('status', '=', $request->status);

                $filter['status'] = Status::getNameById($request->status);
            } else {
                $bills->where('status', '!=', 0);
            }

            $bills->where('created_by', '=', \Auth::user()->creatorId());
            $bills = $bills->get();


            $totalBill      = 0;
            $totalDueBill   = 0;
            $totalPaidBill  = 0;
            $billTotalArray = [];
            foreach ($bills as $bill) {
                $totalBill    += $bill->getTotal();
                $totalDueBill += $bill->getDue();
                $totalPaidBill += $bill->getPaymentsTotal();

                $billTotalArray[$bill->month][] = $bill->getTotal();
            }

            for ($i = 1; $i <= 12; $i++) {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            return view('report.bill_report', compact('bills', 'vender', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function accountStatement(Request $request)
    {

        if (\Auth::user()->can('statement report')) {

            $filter['account']             = __('All');
            $filter['type']                = __('Revenue');
            $reportData['revenues']        = '';
            $reportData['payments']        = '';
            $reportData['revenueAccounts'] = '';
            $reportData['paymentAccounts'] = '';

            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');

            $types = [
                'revenue' => __('Revenue'),
                'payment' => __('Payment'),
            ];

            if ($request->type == 'revenue' || !isset($request->type)) {

                $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total')->where('revenues.created_by', '=', \Auth::user()->creatorId());

                $revenues = Revenue::where('revenues.created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc');
            }

            if ($request->type == 'payment') {
                $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total')->where('payments.created_by', '=', \Auth::user()->creatorId());

                $payments = Payment::where('payments.created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc');
            }


            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-m'));
                $end   = strtotime(date('Y-m', strtotime("-5 month")));
            }


            $currentdate = $start;
            while ($currentdate <= $end) {
                $data['month'] = date('m', $currentdate);
                $data['year']  = date('Y', $currentdate);

                if ($request->type == 'revenue' || !isset($request->type)) {
                    $revenues->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('revenues.created_by', '=', \Auth::user()->creatorId());
                        }
                    );

                    $revenueAccounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('revenues.created_by', '=', \Auth::user()->creatorId());
                        }
                    );
                }

                if ($request->type == 'payment') {
                    $paymentAccounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('payments.created_by', '=', \Auth::user()->creatorId());
                        }
                    );
                }


                $currentdate = strtotime('+1 month', $currentdate);
            }

            if (!empty($request->account)) {
                if ($request->type == 'revenue' || !isset($request->type)) {
                    $revenues->where('account_id', $request->account);
                    $revenues->where('revenues.created_by', '=', \Auth::user()->creatorId());
                    $revenueAccounts->where('account_id', $request->account);
                    $revenueAccounts->where('revenues.created_by', '=', \Auth::user()->creatorId());
                }

                if ($request->type == 'payment') {
                    $payments->where('account_id', $request->account);
                    $payments->where('payments.created_by', '=', \Auth::user()->creatorId());

                    $paymentAccounts->where('account_id', $request->account);
                    $paymentAccounts->where('payments.created_by', '=', \Auth::user()->creatorId());
                }


                $bankAccount       = BankAccount::find($request->account);
                $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                if ($bankAccount->holder_name == 'Cash') {
                    $filter['account'] = 'Cash';
                }
            }

            if ($request->type == 'revenue' || !isset($request->type)) {
                $reportData['revenues'] = $revenues->get();

                $revenueAccounts->where('revenues.created_by', '=', \Auth::user()->creatorId());
                $reportData['revenueAccounts'] = $revenueAccounts->get();
            }

            if ($request->type == 'payment') {
                $reportData['payments'] = $payments->get();

                $paymentAccounts->where('payments.created_by', '=', \Auth::user()->creatorId());
                $reportData['paymentAccounts'] = $paymentAccounts->get();
                $filter['type']                = __('Payment');
            }


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            return view('report.statement_report', compact('reportData', 'account', 'types', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // public function balanceSheet(Request $request)
    // {

    //     if (\Auth::user()->can('bill report')) {

    //         if (!empty($request->start_date) && !empty($request->end_date)) {
    //             $start = $request->start_date;
    //             $end   = $request->end_date;
    //         } else {
    //             $start = date('Y-m-01');
    //             $end   = date('Y-m-t');
    //         }

    //         $types = ChartOfAccountType::get();


    //         $chartAccounts = [];
    //         foreach ($types as $type) {
    //             $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();

    //             $subTypeArray = [];
    //             foreach ($subTypes as $subType) {
    //                 $accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->where('type', $type->id)->where('sub_type', $subType->id)->get();

    //                 $accountArray = [];
    //                 foreach ($accounts as $account) {

    //                     $journalItem = JournalItem::select(\DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $account->id);
    //                     $journalItem->where('created_at', '>=', $start);
    //                     $journalItem->where('created_at', '<=', $end);
    //                     $journalItem          = $journalItem->first();
    //                     $data['account_name'] = $account->name;
    //                     $data['totalCredit']  = $journalItem->totalCredit;
    //                     $data['totalDebit']   = $journalItem->totalDebit;
    //                     $data['netAmount']    = $journalItem->netAmount;
    //                     $accountArray[]       = $data;
    //                 }
    //                 $subTypeData['subType'] = $subType->name;
    //                 $subTypeData['account'] = $accountArray;
    //                 $subTypeArray[]         = $subTypeData;
    //             }

    //             $chartAccounts[$type->name] = $subTypeArray;
    //         }

    //         $filter['startDateRange'] = $start;
    //         $filter['endDateRange']   = $end;


    //         return view('report.balance_sheet', compact('filter', 'chartAccounts'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission Denied.'));
    //     }
    // }

    public function balanceSheet(Request $request, $view = '', $collapseview = 'expand')
    {
        if (\Auth::user()->can('bill report')) {
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-01-01');
                $end = date('Y-m-d', strtotime('+1 day'));
            }
            $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Assets', 'Liabilities', 'Equity'])->get();
            $totalAccounts = [];
            foreach ($types as $type) {
                $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();

                $subTypeArray = [];
                foreach ($subTypes as $subType) {
                    $parentAccounts = ChartOfAccountParent::where('sub_type', $subType->id)->get();
                    // $parentAccountArray = [];
                    // $parentAccountArrayTotal = [];

                    $totalParentAccountArray = [];
                    if ($parentAccounts->isNotEmpty()) {
                        foreach ($parentAccounts as $parentAccount) {
                            $totalArray = [];
                            $parentAccountArray = [];
                            $parentAccountArrayTotal = [];

                            $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                            $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                            $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.sub_type', $subType->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('transaction_lines.created_by', \Auth::user()->creatorId());
                            $parentAccs->where('transaction_lines.date', '>=', $start);
                            $parentAccs->where('transaction_lines.date', '<=', $end);
                            $parentAccs->groupBy('account_id');
                            $parentAccs = $parentAccs->get()->toArray();

                            $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                            $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                            $accounts->where('chart_of_accounts.type', $type->id);
                            $accounts->where('chart_of_accounts.sub_type', $subType->id);
                            $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                            $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                            $accounts->where('transaction_lines.date', '>=', $start);
                            $accounts->where('transaction_lines.date', '<=', $end);
                            $accounts->groupBy('account_id');
                            $accounts = $accounts->get()->toArray();

                            if ($parentAccs == [] && $accounts != []) {

                                $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                                $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                                $parentAccs->where('chart_of_accounts.type', $type->id);
                                $parentAccs->where('chart_of_accounts.sub_type', $subType->id);
                                $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                                $parentAccs->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                                $parentAccs = $parentAccs->get()->toArray();
                            }

                            if ($parentAccs != [] && $accounts == []) {

                                $parentAccs = [];
                            }

                            $parenttotalBalance = 0;
                            $parentcreditTotal = 0;
                            $parenntdebitTotal = 0;
                            $parenttotalAmount = 0;

                            foreach ($parentAccs as $account) {
                                $Balance = $account['totalCredit'] - $account['totalDebit'];
                                $parenttotalBalance += $Balance;

                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'parent',
                                    'totalCredit' => 0,
                                    'totalDebit' => 0,
                                    'netAmount' => $Balance,
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                                $parenttotalAmount += $data['netAmount'];
                            }

                            foreach ($accounts as $account) {
                                $Balance = $account['totalCredit'] - $account['totalDebit'];
                                $parenttotalBalance += $Balance;

                                if ($Balance != 0) {
                                    $data = [
                                        'account_id' => $account['id'],
                                        'account_code' => $account['code'],
                                        'account_name' => $account['name'],
                                        'account' => 'subAccount',
                                        'totalCredit' => 0,
                                        'totalDebit' => 0,
                                        'netAmount' => $Balance,
                                    ];

                                    $parentAccountArray[] = $data;
                                    $parentcreditTotal += $data['totalCredit'];
                                    $parenntdebitTotal += $data['totalDebit'];
                                    $parenttotalAmount += $data['netAmount'];
                                }
                            }

                            if (!empty($parentAccountArray)) {
                                $dataTotal = [
                                    'account_id' => $parentAccount->account,
                                    'account_code' => '',
                                    'account' => 'parentTotal',
                                    'account_name' => 'Total ' . $parentAccount->name,
                                    'totalCredit' => $parentcreditTotal,
                                    'totalDebit' => $parenntdebitTotal,
                                    'netAmount' => $parenttotalAmount,
                                ];

                                $parentAccountArrayTotal[] = $dataTotal;
                                $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                                $totalParentAccountArray[] = $totalArray;
                            }

                        }

                    }
                    if ($totalParentAccountArray != []) {
                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.sub_type', $subType->id);
                        $accounts->where('chart_of_account_parents.account');
                        $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                        $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();
                    } else {
                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.sub_type', $subType->id);
                        $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();
                    }

                    $totalBalance = 0;
                    $creditTotal = 0;
                    $debitTotal = 0;
                    $totalAmount = 0;
                    $accountArray = [];

                    foreach ($accounts as $account) {
                        $Balance = $account['totalCredit'] - $account['totalDebit'];
                        $totalBalance += $Balance;
                        if ($Balance != 0) {
                            $data['account_id'] = $account['id'];
                            $data['account_code'] = $account['code'];
                            $data['account_name'] = $account['name'];
                            $data['account'] = '';
                            $data['totalCredit'] = 0;
                            $data['totalDebit'] = 0;
                            $data['netAmount'] = $Balance;
                            $accountArray[][] = $data;
                            $creditTotal += $data['totalCredit'];
                            $debitTotal += $data['totalDebit'];
                            $totalAmount += $data['netAmount'];
                        }
                    }

                    $totalAccountArray = [];
                    if ($accountArray != []) {
                        $dataTotal['account_id'] = '';
                        $dataTotal['account_code'] = '';
                        $dataTotal['account'] = '';
                        $dataTotal['account_name'] = 'Total ' . $subType->name;
                        $dataTotal['totalCredit'] = $creditTotal;
                        $dataTotal['totalDebit'] = $debitTotal;

                        if (isset($totalParentAccountArray) && $totalParentAccountArray != []) {

                            $netAmount = 0;

                            foreach ($totalParentAccountArray as $innerArray) {
                                $lastElement = end($innerArray);

                                $netAmount += $lastElement['netAmount'];
                            }

                            $dataTotal['netAmount'] = $netAmount + $totalAmount;
                        } else {
                            $dataTotal['netAmount'] = $totalAmount;
                        }
                        $accountArrayTotal[][] = $dataTotal;
                        $totalAccountArray = array_merge($totalParentAccountArray, $accountArray, $accountArrayTotal);

                    } elseif ($totalParentAccountArray != []) {
                        $dataTotal['account_id'] = '';
                        $dataTotal['account_code'] = '';
                        $dataTotal['account'] = '';
                        $dataTotal['account_name'] = 'Total ' . $subType->name;
                        $dataTotal['totalCredit'] = $creditTotal;
                        $dataTotal['totalDebit'] = $debitTotal;
                        $netAmount = 0;

                        foreach ($totalParentAccountArray as $innerArray) {
                            $lastElement = end($innerArray);

                            $netAmount += $lastElement['netAmount'];
                        }
                        $dataTotal['netAmount'] = $netAmount;
                        $accountArrayTotal[][] = $dataTotal;
                        $totalAccountArray = array_merge($totalParentAccountArray, $accountArrayTotal);
                    }

                    if ($totalAccountArray != []) {
                        $subTypeData['subType'] = ($totalAccountArray != []) ? $subType->name : '';
                        $subTypeData['account'] = $totalAccountArray;
                        $subTypeArray[] = ($subTypeData['account'] != [] && $subTypeData['subType'] != []) ? $subTypeData : [];
                    }
                }
                $totalAccounts[$type->name] = $subTypeArray;
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;

            if ($request->view == 'horizontal' || $view == 'horizontal') {
                return view('report.balance_sheet_horizontal', compact('filter', 'totalAccounts', 'collapseview'));
            } elseif ($view == '' || $view == 'vertical') {
                return view('report.balance_sheet', compact('filter', 'totalAccounts', 'collapseview'));
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function balanceSheetExport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = date('Y-m-01');
            $end = date('Y-m-t', strtotime('+1 day'));
        }

        $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Assets', 'Liabilities', 'Equity'])->get();

        foreach ($types as $type) {
            $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();

            $subTypeArray = [];
            foreach ($subTypes as $subType) {
                $parentAccounts = ChartOfAccountParent::where('sub_type', $subType->id)->get();
                $totalParentAccountArray = [];
                if ($parentAccounts->isNotEmpty()) {
                    foreach ($parentAccounts as $parentAccount) {
                        $totalArray = [];
                        $parentAccountArray = [];
                        $parentAccountArrayTotal = [];

                        $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $parentAccs->where('chart_of_accounts.type', $type->id);
                        $parentAccs->where('chart_of_accounts.sub_type', $subType->id);
                        $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                        $parentAccs->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $parentAccs->where('transaction_lines.date', '>=', $start);
                        $parentAccs->where('transaction_lines.date', '<=', $end);
                        $parentAccs->groupBy('account_id');
                        $parentAccs = $parentAccs->get()->toArray();

                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.sub_type', $subType->id);
                        $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                        $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();

                        if ($parentAccs == [] && $accounts != []) {

                            $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                            $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.sub_type', $subType->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                            $parentAccs = $parentAccs->get()->toArray();
                        }

                        if ($parentAccs != [] && $accounts == []) {

                            $parentAccs = [];
                        }

                        $parenttotalBalance = 0;
                        $parentcreditTotal = 0;
                        $parenntdebitTotal = 0;
                        $parenttotalAmount = 0;

                        foreach ($parentAccs as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            $data = [
                                'account_id' => $account['id'],
                                'account_code' => $account['code'],
                                'account_name' => $account['name'],
                                'account' => 'parent',
                                'totalCredit' => 0,
                                'totalDebit' => 0,
                                'netAmount' => $Balance,
                            ];

                            $parentAccountArray[] = $data;
                            $parentcreditTotal += $data['totalCredit'];
                            $parenntdebitTotal += $data['totalDebit'];
                            $parenttotalAmount += $data['netAmount'];
                        }

                        foreach ($accounts as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            if ($Balance != 0) {
                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'subAccount',
                                    'totalCredit' => 0,
                                    'totalDebit' => 0,
                                    'netAmount' => $Balance,
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                                $parenttotalAmount += $data['netAmount'];
                            }
                        }

                        if (!empty($parentAccountArray)) {
                            $dataTotal = [
                                'account_id' => $parentAccount->account,
                                'account_code' => '',
                                'account' => 'parentTotal',
                                'account_name' => 'Total ' . $parentAccount->name,
                                'totalCredit' => $parentcreditTotal,
                                'totalDebit' => $parenntdebitTotal,
                                'netAmount' => $parenttotalAmount,
                            ];

                            $parentAccountArrayTotal[] = $dataTotal;
                            $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                            $totalParentAccountArray[] = $totalArray;
                        }

                    }

                }
                if ($totalParentAccountArray != []) {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_accounts.sub_type', $subType->id);
                    $accounts->where('chart_of_account_parents.account');
                    $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                } else {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_accounts.sub_type', $subType->id);
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                }

                $totalBalance = 0;
                $creditTotal = 0;
                $debitTotal = 0;
                $totalAmount = 0;
                $accountArray = [];

                foreach ($accounts as $account) {
                    $Balance = $account['totalCredit'] - $account['totalDebit'];
                    $totalBalance += $Balance;
                    if ($Balance != 0) {
                        $data['account_id'] = $account['id'];
                        $data['account_code'] = $account['code'];
                        $data['account_name'] = $account['name'];
                        $data['account'] = '';
                        $data['totalCredit'] = 0;
                        $data['totalDebit'] = 0;
                        $data['netAmount'] = $Balance;
                        $accountArray[][] = $data;
                        $creditTotal += $data['totalCredit'];
                        $debitTotal += $data['totalDebit'];
                        $totalAmount += $data['netAmount'];
                    }
                }

                $totalAccountArray = [];
                if ($accountArray != []) {
                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $subType->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;

                    if (isset($totalParentAccountArray) && $totalParentAccountArray != []) {

                        $netAmount = 0;

                        foreach ($totalParentAccountArray as $innerArray) {
                            $lastElement = end($innerArray);

                            $netAmount += $lastElement['netAmount'];
                        }

                        $dataTotal['netAmount'] = $netAmount + $totalAmount;
                    } else {
                        $dataTotal['netAmount'] = $totalAmount;
                    }
                    $accountArrayTotal[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArray, $accountArrayTotal);

                } elseif ($totalParentAccountArray != []) {
                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $subType->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;
                    $netAmount = 0;

                    foreach ($totalParentAccountArray as $innerArray) {
                        $lastElement = end($innerArray);

                        $netAmount += $lastElement['netAmount'];
                    }
                    $dataTotal['netAmount'] = $netAmount;
                    $accountArrayTotal[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArrayTotal);
                }

                if ($totalAccountArray != []) {
                    $subTypeData['subType'] = ($totalAccountArray != []) ? $subType->name : '';
                    $subTypeData['account'] = $totalAccountArray;
                    $subTypeArray[] = ($subTypeData['account'] != [] && $subTypeData['subType'] != []) ? $subTypeData : [];
                }
            }
            $totalAccounts[$type->name] = $subTypeArray;
        }

        $companyName = User::where('id', \Auth::user()->creatorId())->first();
        $companyName = $companyName->name;

        $name = 'balance_sheet_' . date('Y-m-d i:h:s');
        $data = Excel::download(new BalanceSheetExport($totalAccounts, $start, $end, $companyName), $name . '.xlsx');

        ob_end_clean();


        return $data;

    }

    public function ledgerSummary(Request $request)
    {
        if (\Auth::user()->can('ledger report')) {
            $accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end   = $request->end_date;
            } else {
                $start = date('Y-m-01');
                $end   = date('Y-m-t');
            }

            if (!empty($request->account)) 
            {
                $chart_accounts = ChartOfAccount::where('id', $request->account)->where('created_by', \Auth::user()->creatorId())->get();
                $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                    ->where('parent', '=', 0)
                    ->where('created_by', \Auth::user()->creatorId())->get()
                    ->toarray();
            } 
            else 
            {
                $chart_accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->get();
                $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                    ->where('parent', '=', 0)
                    ->where('created_by', \Auth::user()->creatorId())->get()
                    ->toarray();
            }
            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();

            $balance = 0;
            $debit   = 0;
            $credit  = 0;
            $filter['balance']        = $balance;
            $filter['credit']         = $credit;
            $filter['debit']          = $debit;
            $filter['startDateRange'] = $start;
            $filter['endDateRange']   = $end;

            return view('report.ledger_summary', compact('filter', 'accounts', 'subAccounts', 'chart_accounts'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // public function trialBalanceSummary(Request $request)
    // {
    //     if (\Auth::user()->can('trial balance report')) {


    //         if (!empty($request->start_date) && !empty($request->end_date)) {
    //             $start = $request->start_date;
    //             $end   = $request->end_date;
    //         } else {
    //             $start = date('Y-m-01');
    //             $end   = date('Y-m-t');
    //         }

    //         $journalItem = JournalItem::select('chart_of_accounts.name', \DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'));
    //         $journalItem->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal');
    //         $journalItem->leftjoin('chart_of_accounts', 'journal_items.account', 'chart_of_accounts.id');
    //         $journalItem->where('journal_items.created_at', '>=', $start);
    //         $journalItem->where('journal_items.created_at', '<=', $end);
    //         $journalItem->groupBy('account');
    //         $journalItem = $journalItem->get()->toArray();

    //         $filter['startDateRange'] = $start;
    //         $filter['endDateRange']   = $end;

    //         return view('report.trial_balance', compact('filter', 'journalItem'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission Denied.'));
    //     }
    // }

    public function trialBalanceSummary(Request $request, $view = "expand")
    {
        if (\Auth::user()->can('trial balance report')) {

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-01-01');
                $end = date('Y-m-d', strtotime('+1 day'));
            }
            $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->get();
            $totalAccounts = [];
            $totalAccount = [];
            foreach ($types as $type) {

                // $type->id = 4;
                $parentAccounts = ChartOfAccountParent::where('type', $type->id)->where('created_by', \Auth::user()->creatorId())->get();

                $totalParentAccountArray = [];
                if ($parentAccounts->isNotEmpty()) {
                    foreach ($parentAccounts as $parentAccount) {
                        $totalArray = [];
                        $parentAccountArray = [];
                        $parentAccountArrayTotal = [];

                        $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $parentAccs->where('chart_of_accounts.type', $type->id);
                        $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                        $parentAccs->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $parentAccs->where('transaction_lines.date', '>=', $start);
                        $parentAccs->where('transaction_lines.date', '<=', $end);
                        $parentAccs->groupBy('account_id');
                        $parentAccs = $parentAccs->get()->toArray();

                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                        $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();

                        if ($parentAccs == [] && $accounts != []) {

                            $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                            $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                            $parentAccs = $parentAccs->get()->toArray();
                        } elseif ($parentAccs != [] && $accounts == []) {
                            $parentAccs = [];

                        }

                        $parenttotalBalance = 0;
                        $parentcreditTotal = 0;
                        $parenntdebitTotal = 0;
                        $parenttotalAmount = 0;

                        foreach ($parentAccs as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            $data = [
                                'account_id' => $account['id'],
                                'account_code' => $account['code'],
                                'account_name' => $account['name'],
                                'account' => 'parent',
                                'totalCredit' => $account['totalCredit'],
                                'totalDebit' => $account['totalDebit'],
                            ];

                            $parentAccountArray[] = $data;
                            $parentcreditTotal += $data['totalCredit'];
                            $parenntdebitTotal += $data['totalDebit'];
                        }

                        foreach ($accounts as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];

                            if ($Balance != 0) {
                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'subAccount',
                                    'totalCredit' => $account['totalCredit'],
                                    'totalDebit' => $account['totalDebit'],
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                            }
                        }

                        if (!empty($parentAccountArray)) {

                            $dataTotal = [
                                'account_id' => $parentAccount->account,
                                'account_code' => '',
                                'account' => 'parentTotal',
                                'account_name' => 'Total ' . $parentAccount->name,
                                'totalCredit' => $parentcreditTotal,
                                'totalDebit' => $parenntdebitTotal,
                            ];

                            $parentAccountArrayTotal[] = $dataTotal;
                        }

                        if ($parentAccountArray != []) {
                            $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                            $totalParentAccountArray[] = $totalArray;
                        }
                    }
                }

                if ($totalParentAccountArray != []) {
                    $accounts = TransactionLines::select('chart_of_accounts.id as account_id', 'chart_of_accounts.code as account_code', 'chart_of_accounts.name as account_name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_account_parents.account');
                    $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                } else {
                    $accounts = TransactionLines::select('chart_of_accounts.id as account_id', 'chart_of_accounts.code as account_code', 'chart_of_accounts.name as account_name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                }

                $name = $type->name;
                if (isset($totalAccount[$name])) {
                    $totalAccount[$name]["totalCredit"] += $accounts["totalCredit"];
                    $totalAccount[$name]["totalDebit"] += $accounts["totalDebit"];
                } else {
                    $totalAccount[$name] = $accounts;
                }
                if ($totalParentAccountArray != []) {
                    $totalAccount[$name] = array_merge_recursive($totalAccount[$name], $totalParentAccountArray[0]);
                }
            }

            foreach ($totalAccount as $category => $entries) {
                foreach ($entries as $entry) {
                    $name = $entry['account_name'];
                    if (!isset($totalAccounts[$category][$name])) {
                        $totalAccounts[$category][$name] = [
                            'account_id' => $entry['account_id'],
                            'account_code' => $entry['account_code'],
                            'account_name' => $name,
                            'account' => isset($entry['account']) ? $entry['account'] : '',
                            'totalDebit' => 0,
                            'totalCredit' => 0,
                        ];
                    }
                    if ($entry['totalDebit'] < 0) {
                        $totalAccounts[$category][$name]['totalDebit'] += 0;
                        $totalAccounts[$category][$name]['totalCredit'] += -$entry['totalDebit'];
                    } else {
                        $totalAccounts[$category][$name]['totalDebit'] += $entry['totalDebit'];
                        $totalAccounts[$category][$name]['totalCredit'] += $entry['totalCredit'];
                    }
                }
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            return view('report.trial_balance', compact('filter', 'totalAccounts', 'view'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function trialBalanceExport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = date('Y-m-01');
            $end = date('Y-m-t', strtotime('+1 day'));
        }

        $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->get();
        $chartAccounts = [];

        $totalAccounts = [];
        $totalAccounts = [];

        foreach ($types as $type) {

            // $type->id = 4;
            $parentAccounts = ChartOfAccountParent::where('type', $type->id)->where('created_by', \Auth::user()->creatorId())->get();

            $totalParentAccountArray = [];
            if ($parentAccounts->isNotEmpty()) {
                foreach ($parentAccounts as $parentAccount) {
                    $totalArray = [];
                    $parentAccountArray = [];
                    $parentAccountArrayTotal = [];

                    $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $parentAccs->where('chart_of_accounts.type', $type->id);
                    $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                    $parentAccs->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $parentAccs->where('transaction_lines.date', '>=', $start);
                    $parentAccs->where('transaction_lines.date', '<=', $end);
                    $parentAccs->groupBy('account_id');
                    $parentAccs = $parentAccs->get()->toArray();

                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                    $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                    $accounts->where('transaction_lines.date', '>=', $start);
                    $accounts->where('transaction_lines.date', '<=', $end);
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();

                    if ($parentAccs == [] && $accounts != []) {

                        $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                        $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                        $parentAccs->where('chart_of_accounts.type', $type->id);
                        $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                        $parentAccs->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                        $parentAccs = $parentAccs->get()->toArray();
                    } elseif ($parentAccs != [] && $accounts == []) {
                        $parentAccs = [];

                    }

                    $parenttotalBalance = 0;
                    $parentcreditTotal = 0;
                    $parenntdebitTotal = 0;
                    $parenttotalAmount = 0;

                    foreach ($parentAccs as $account) {
                        $Balance = $account['totalCredit'] - $account['totalDebit'];
                        $parenttotalBalance += $Balance;

                        $data = [
                            'account_id' => $account['id'],
                            'account_code' => $account['code'],
                            'account_name' => $account['name'],
                            'account' => 'parent',
                            'totalCredit' => $account['totalCredit'],
                            'totalDebit' => $account['totalDebit'],
                        ];

                        $parentAccountArray[] = $data;
                        $parentcreditTotal += $data['totalCredit'];
                        $parenntdebitTotal += $data['totalDebit'];
                    }

                    foreach ($accounts as $account) {
                        $Balance = $account['totalCredit'] - $account['totalDebit'];

                        if ($Balance != 0) {
                            $data = [
                                'account_id' => $account['id'],
                                'account_code' => $account['code'],
                                'account_name' => $account['name'],
                                'account' => 'subAccount',
                                'totalCredit' => $account['totalCredit'],
                                'totalDebit' => $account['totalDebit'],
                            ];

                            $parentAccountArray[] = $data;
                            $parentcreditTotal += $data['totalCredit'];
                            $parenntdebitTotal += $data['totalDebit'];
                        }
                    }

                    if (!empty($parentAccountArray)) {

                        $dataTotal = [
                            'account_id' => $parentAccount->account,
                            'account_code' => '',
                            'account' => 'parentTotal',
                            'account_name' => 'Total ' . $parentAccount->name,
                            'totalCredit' => $parentcreditTotal,
                            'totalDebit' => $parenntdebitTotal,
                        ];

                        $parentAccountArrayTotal[] = $dataTotal;
                    }

                    if ($parentAccountArray != []) {
                        $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                        $totalParentAccountArray[] = $totalArray;
                    }
                }
            }

            if ($totalParentAccountArray != []) {
                $accounts = TransactionLines::select('chart_of_accounts.id as account_id', 'chart_of_accounts.code as account_code', 'chart_of_accounts.name as account_name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                $accounts->where('chart_of_accounts.type', $type->id);
                $accounts->where('chart_of_account_parents.account');
                $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                $accounts->where('transaction_lines.date', '>=', $start);
                $accounts->where('transaction_lines.date', '<=', $end);
                $accounts->groupBy('account_id');
                $accounts = $accounts->get()->toArray();
            } else {
                $accounts = TransactionLines::select('chart_of_accounts.id as account_id', 'chart_of_accounts.code as account_code', 'chart_of_accounts.name as account_name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                $accounts->where('chart_of_accounts.type', $type->id);
                $accounts->where('transaction_lines.created_by', \Auth::user()->creatorId());
                $accounts->where('transaction_lines.date', '>=', $start);
                $accounts->where('transaction_lines.date', '<=', $end);
                $accounts->groupBy('account_id');
                $accounts = $accounts->get()->toArray();
            }

            $name = $type->name;
            if (isset($totalAccount[$name])) {
                $totalAccount[$name]["totalCredit"] += $accounts["totalCredit"];
                $totalAccount[$name]["totalDebit"] += $accounts["totalDebit"];
            } else {
                $totalAccount[$name] = $accounts;
            }
            if ($totalParentAccountArray != []) {
                $totalAccount[$name] = array_merge_recursive($totalAccount[$name], $totalParentAccountArray[0]);
            }
        }

        foreach ($totalAccount as $category => $entries) {
            foreach ($entries as $entry) {
                $name = $entry['account_name'];
                if (!isset($totalAccounts[$category][$name])) {
                    $totalAccounts[$category][$name] = [
                        'account_id' => $entry['account_id'],
                        'account_code' => $entry['account_code'],
                        'account_name' => $name,
                        'account' => isset($entry['account']) ? $entry['account'] : '',
                        'totalDebit' => 0,
                        'totalCredit' => 0,
                    ];
                }
                if ($entry['totalDebit'] < 0) {
                    $totalAccounts[$category][$name]['totalDebit'] += 0;
                    $totalAccounts[$category][$name]['totalCredit'] += -$entry['totalDebit'];
                } else {
                    $totalAccounts[$category][$name]['totalDebit'] += $entry['totalDebit'];
                    $totalAccounts[$category][$name]['totalCredit'] += $entry['totalCredit'];
                }
            }
        }
        $companyName = User::where('id', \Auth::user()->creatorId())->first();
        $companyName = $companyName->name;

        $name = 'trial_balance_' . date('Y-m-d i:h:s');
        $data = Excel::download(new TrialBalancExport($totalAccounts, $start, $end, $companyName), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function budgetExecution(Request $request, BudgetExecutionReportService $reportService)
    {
        if (!\Auth::user()->can('reportes_presupuesto_view')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $budgets = Budget::where('created_by', $creatorId)
            ->orderByDesc('from')
            ->orderByDesc('id')
            ->get();

        $classifier = $request->get('classifier', BudgetExecutionReportService::CLASSIFIER_OBJECT);
        $report = $reportService->reporteEjecucion($creatorId, [
            'budget_id' => $request->get('budget_id'),
            'classifier' => $classifier,
        ]);

        $selectedBudgetId = $request->get('budget_id') ?: ($report['budget']?->id);
        $classifierOptions = $reportService->classifierOptions();

        return view('report.budget_execution', [
            'budgets' => $budgets,
            'selectedBudgetId' => $selectedBudgetId,
            'classifier' => $classifier,
            'classifierOptions' => $classifierOptions,
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'budget' => $report['budget'],
            'budgetLabel' => $report['budgetLabel'],
        ]);
    }

    public function budgetExecutionExport(Request $request, BudgetExecutionReportService $reportService)
    {
        if (!\Auth::user()->can('reportes_presupuesto_view')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $report = $reportService->reporteEjecucion($creatorId, [
            'budget_id' => $request->get('budget_id'),
            'classifier' => $request->get('classifier', BudgetExecutionReportService::CLASSIFIER_OBJECT),
        ]);

        $classifierOptions = $reportService->classifierOptions();
        $classifierLabel = $classifierOptions[$report['classifier']] ?? $report['classifier'];

        $rows = $this->buildBudgetExecutionExportRows(
            $report['rows'],
            $report['totals'],
            $report['budgetLabel'],
            $classifierLabel
        );

        $name = 'budget_execution_' . date('Y-m-d_H-i-s');
        $data = Excel::download(new BudgetExecutionExport($rows), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function publicBudgetExecution(Request $request, BudgetExecutionReportService $reportService)
    {
        if (!\Auth::user()->can('reportes_financieros_publicos_view')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $budgets = Budget::where('created_by', $creatorId)
            ->orderByDesc('from')
            ->orderByDesc('id')
            ->get();

        $classifier = $request->get('classifier', BudgetExecutionReportService::CLASSIFIER_OBJECT);
        $report = $reportService->estadoEjecucion($creatorId, [
            'budget_id' => $request->get('budget_id'),
            'classifier' => $classifier,
        ]);

        $selectedBudgetId = $request->get('budget_id') ?: ($report['budget']?->id);
        $classifierOptions = $reportService->publicClassifierOptions();
        $companyName = User::where('id', $creatorId)->value('name') ?? '';

        return view('report.public_budget_execution', [
            'budgets' => $budgets,
            'selectedBudgetId' => $selectedBudgetId,
            'classifier' => $classifier,
            'classifierOptions' => $classifierOptions,
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'budget' => $report['budget'],
            'budgetLabel' => $report['budgetLabel'],
            'companyName' => $companyName,
        ]);
    }

    public function publicBudgetExecutionExport(Request $request, BudgetExecutionReportService $reportService)
    {
        if (!\Auth::user()->can('reportes_financieros_publicos_view')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $report = $reportService->estadoEjecucion($creatorId, [
            'budget_id' => $request->get('budget_id'),
            'classifier' => $request->get('classifier', BudgetExecutionReportService::CLASSIFIER_OBJECT),
        ]);

        $classifierOptions = $reportService->publicClassifierOptions();
        $classifierLabel = $classifierOptions[$report['classifier']] ?? $report['classifier'];
        $companyName = User::where('id', $creatorId)->value('name') ?? '';

        $rows = $this->buildPublicBudgetExecutionExportRows(
            $report['rows'],
            $report['totals'],
            $report['budgetLabel'],
            $classifierLabel,
            $companyName
        );

        $name = 'public_budget_execution_' . date('Y-m-d_H-i-s');
        $data = Excel::download(new PublicBudgetExecutionExport($rows), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function publicFinancialPosition(Request $request, PublicFinancialPositionService $service)
    {
        if (!\Auth::user()->can('reportes_financieros_publicos_view')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cutoffDate = $request->get('cutoff_date', date('Y-m-d'));
        $creatorId = \Auth::user()->creatorId();
        $report = $service->buildReport($creatorId, $cutoffDate);

        return view('report.public_financial_position', compact('cutoffDate', 'report'));
    }

    public function publicFinancialPositionExport(Request $request, PublicFinancialPositionService $service)
    {
        if (!\Auth::user()->can('reportes_financieros_publicos_view')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'cutoff_date' => 'required|date',
        ]);

        $creatorId = \Auth::user()->creatorId();
        $companyName = User::where('id', $creatorId)->value('name') ?? '';
        $report = $service->buildReport($creatorId, $request->cutoff_date);

        return Excel::download(
            new PublicFinancialPositionExport($report, $request->cutoff_date, $companyName),
            'estado-situacion-financiera.xlsx'
        );
    }

    public function publicFinancialPositionPdf(Request $request, PublicFinancialPositionService $service)
    {
        if (!\Auth::user()->can('reportes_financieros_publicos_view')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'cutoff_date' => 'required|date',
        ]);

        $creatorId = \Auth::user()->creatorId();
        $companyName = User::where('id', $creatorId)->value('name') ?? '';
        $report = $service->buildReport($creatorId, $request->cutoff_date);

        $pdf = Pdf::loadView('report.public_financial_position_pdf', [
            'cutoffDate' => $request->cutoff_date,
            'report' => $report,
            'companyName' => $companyName,
        ]);

        return $pdf->download('estado-situacion-financiera.pdf');
    }

    public function fondosMovimientos(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $selectedFondo = $request->get('fondo_id');

        $fondos = FondoRotatorio::where('created_by', $creatorId)->orderBy('nombre')->get();

        $movimientosQuery = MovimientoFondo::with('fondo')
            ->where('created_by', $creatorId)
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate)
            ->orderBy('fecha', 'desc');

        if (!empty($selectedFondo)) {
            $movimientosQuery->where('fondo_id', $selectedFondo);
        }

        $movimientos = $movimientosQuery->get();

        return view('report.fondos_movimientos', compact('movimientos', 'fondos', 'startDate', 'endDate', 'selectedFondo'));
    }

    public function fondosMovimientosExport(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $selectedFondo = $request->get('fondo_id');

        $movimientosQuery = MovimientoFondo::with('fondo')
            ->where('created_by', $creatorId)
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate)
            ->orderBy('fecha', 'desc');

        if (!empty($selectedFondo)) {
            $movimientosQuery->where('fondo_id', $selectedFondo);
        }

        $movimientos = $movimientosQuery->get();
        $fondoNombre = '';
        if (!empty($selectedFondo)) {
            $fondoNombre = FondoRotatorio::where('created_by', $creatorId)
                ->where('id', $selectedFondo)
                ->value('nombre') ?? '';
        }

        $name = 'fund_movements_' . date('Y-m-d_H-i-s');
        $data = Excel::download(new FondoMovimientosExport($movimientos, $startDate, $endDate, $fondoNombre), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function recaudacionesDiarias(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $startDate = $request->get('start_date', now()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $selectedServicio = $request->get('servicio');
        $selectedMetodo = $request->get('metodo_pago');
        $selectedCuenta = $request->get('cuenta_recaudadora_id');

        $recaudacionesQuery = Recaudacion::with('cuentaRecaudadora')
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate)
            ->orderBy('fecha', 'desc');

        $cuentasQuery = CuentaRecaudadora::query()->orderBy('banco')->orderBy('numero_cuenta');

        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $recaudacionesQuery->where('created_by', $creatorId);
            $cuentasQuery->where('created_by', $creatorId);
        }

        if (!empty($selectedServicio)) {
            $recaudacionesQuery->where('servicio', $selectedServicio);
        }

        if (!empty($selectedMetodo)) {
            $recaudacionesQuery->where('metodo_pago', $selectedMetodo);
        }

        if (!empty($selectedCuenta)) {
            $recaudacionesQuery->where('cuenta_recaudadora_id', $selectedCuenta);
        }

        $recaudaciones = $recaudacionesQuery->get();
        $cuentas = $cuentasQuery->get();
        $cuentaOptions = $cuentas->mapWithKeys(function ($cuenta) {
            return [$cuenta->id => $cuenta->banco . ' - ' . $cuenta->numero_cuenta];
        });

        $servicioOptions = $this->recaudacionesServicioOptions();
        $metodoPagoOptions = $this->recaudacionesMetodoPagoOptions();

        [$totalsByService, $totalsByMethod, $totalsByAccount] = $this->buildRecaudacionesTotals(
            $recaudaciones,
            $servicioOptions,
            $metodoPagoOptions
        );

        return view('report.recaudaciones_diarias', compact(
            'recaudaciones',
            'cuentas',
            'startDate',
            'endDate',
            'selectedServicio',
            'selectedMetodo',
            'selectedCuenta',
            'servicioOptions',
            'metodoPagoOptions',
            'cuentaOptions',
            'totalsByService',
            'totalsByMethod',
            'totalsByAccount'
        ));
    }

    public function recaudacionesDiariasExport(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $startDate = $request->get('start_date', now()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $selectedServicio = $request->get('servicio');
        $selectedMetodo = $request->get('metodo_pago');
        $selectedCuenta = $request->get('cuenta_recaudadora_id');

        $recaudacionesQuery = Recaudacion::with('cuentaRecaudadora')
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate)
            ->orderBy('fecha', 'desc');

        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $recaudacionesQuery->where('created_by', $creatorId);
        }

        if (!empty($selectedServicio)) {
            $recaudacionesQuery->where('servicio', $selectedServicio);
        }

        if (!empty($selectedMetodo)) {
            $recaudacionesQuery->where('metodo_pago', $selectedMetodo);
        }

        if (!empty($selectedCuenta)) {
            $recaudacionesQuery->where('cuenta_recaudadora_id', $selectedCuenta);
        }

        $recaudaciones = $recaudacionesQuery->get();

        $servicioOptions = $this->recaudacionesServicioOptions();
        $metodoPagoOptions = $this->recaudacionesMetodoPagoOptions();
        [$totalsByService, $totalsByMethod, $totalsByAccount] = $this->buildRecaudacionesTotals(
            $recaudaciones,
            $servicioOptions,
            $metodoPagoOptions
        );

        $name = 'daily_collections_' . date('Y-m-d_H-i-s');
        $data = Excel::download(
            new RecaudacionesDiariasExport(
                $recaudaciones,
                $startDate,
                $endDate,
                $totalsByService,
                $totalsByMethod,
                $totalsByAccount
            ),
            $name . '.xlsx'
        );
        ob_end_clean();

        return $data;
    }

    public function conciliacionBancaria(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_conciliacion_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $selectedEstado = $request->get('estado');

        $movimientosQuery = MovimientoBancario::with(['cuentaRecaudadora', 'conciliable'])
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate)
            ->orderBy('fecha', 'desc');

        if (!empty($selectedEstado)) {
            $movimientosQuery->where('estado_conciliacion', $selectedEstado);
        }

        $movimientos = $movimientosQuery->get();
        $totalsByStatus = $this->buildConciliacionTotals($movimientos);

        return view('report.conciliacion_bancaria', compact(
            'movimientos',
            'startDate',
            'endDate',
            'selectedEstado',
            'totalsByStatus'
        ));
    }

    public function conciliacionBancariaExport(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_conciliacion_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $selectedEstado = $request->get('estado');

        $movimientosQuery = MovimientoBancario::with(['cuentaRecaudadora', 'conciliable'])
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<=', $endDate)
            ->orderBy('fecha', 'desc');

        if (!empty($selectedEstado)) {
            $movimientosQuery->where('estado_conciliacion', $selectedEstado);
        }

        $movimientos = $movimientosQuery->get();
        $totalsByStatus = $this->buildConciliacionTotals($movimientos);

        $name = 'bank_reconciliation_' . date('Y-m-d_H-i-s');
        $data = Excel::download(
            new ConciliacionBancariaExport($movimientos, $startDate, $endDate, $totalsByStatus),
            $name . '.xlsx'
        );
        ob_end_clean();

        return $data;
    }

    public function estadosCuentaConciliacion(Request $request, EstadoCuentaConciliacionService $service)
    {
        if (!\Auth::user()->can('tesoreria_conciliacion_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $selectedCuenta = $request->get('cuenta_recaudadora_id');

        $cuentasQuery = CuentaRecaudadora::query()->orderBy('banco')->orderBy('numero_cuenta');
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $cuentasQuery->where('created_by', $creatorId);
        }

        $cuentas = $cuentasQuery->get();
        $cuentaOptions = $cuentas->mapWithKeys(function ($cuenta) {
            return [$cuenta->id => $cuenta->banco . ' - ' . $cuenta->numero_cuenta];
        })->toArray();

        $selectedCuenta = !empty($selectedCuenta) && in_array((int) $selectedCuenta, $cuentas->pluck('id')->all(), true)
            ? (int) $selectedCuenta
            : null;

        $allowedAccountIds = $cuentas->pluck('id')->all();
        $statementData = $service->buildStatement($selectedCuenta, $allowedAccountIds, $startDate, $endDate);
        $differenceRows = $service->buildDifferences($selectedCuenta, $allowedAccountIds, $startDate, $endDate);
        $differenceTotal = $differenceRows->sum('difference');

        return view('report.estados_cuenta_conciliacion', compact(
            'startDate',
            'endDate',
            'selectedCuenta',
            'cuentaOptions',
            'statementData',
            'differenceRows',
            'differenceTotal'
        ));
    }

    public function estadosCuentaConciliacionExport(Request $request, EstadoCuentaConciliacionService $service)
    {
        if (!\Auth::user()->can('tesoreria_conciliacion_manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $selectedCuenta = $request->get('cuenta_recaudadora_id');

        $cuentasQuery = CuentaRecaudadora::query()->orderBy('banco')->orderBy('numero_cuenta');
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $cuentasQuery->where('created_by', $creatorId);
        }

        $cuentas = $cuentasQuery->get();
        $selectedCuenta = !empty($selectedCuenta) && in_array((int) $selectedCuenta, $cuentas->pluck('id')->all(), true)
            ? (int) $selectedCuenta
            : null;

        $cuenta = $selectedCuenta ? $cuentas->firstWhere('id', $selectedCuenta) : null;
        $allowedAccountIds = $cuentas->pluck('id')->all();
        $statementData = $service->buildStatement($selectedCuenta, $allowedAccountIds, $startDate, $endDate);
        $differenceRows = $service->buildDifferences($selectedCuenta, $allowedAccountIds, $startDate, $endDate);

        $name = 'account_statement_reconciliation_' . date('Y-m-d_H-i-s');
        $data = Excel::download(
            new EstadosCuentaConciliacionExport($startDate, $endDate, $cuenta, $statementData, $differenceRows),
            $name . '.xlsx'
        );
        ob_end_clean();

        return $data;
    }

    private function recaudacionesServicioOptions(): array
    {
        return [
            'consulta' => __('Consultation'),
            'copago' => __('Copay'),
            'privado' => __('Private Service'),
        ];
    }

    private function recaudacionesMetodoPagoOptions(): array
    {
        return [
            'efectivo' => __('Cash'),
            'tarjeta' => __('Card'),
            'transferencia' => __('Transfer'),
            'cheque' => __('Check'),
            'otro' => __('Other'),
        ];
    }

    private function buildRecaudacionesTotals($recaudaciones, array $servicioOptions, array $metodoPagoOptions): array
    {
        $totalsByService = [];
        foreach ($recaudaciones->groupBy('servicio') as $servicio => $items) {
            $label = $servicioOptions[$servicio] ?? $servicio;
            $totalsByService[$label] = $items->sum('monto');
        }

        $totalsByMethod = [];
        foreach ($recaudaciones->groupBy('metodo_pago') as $metodo => $items) {
            $label = $metodoPagoOptions[$metodo] ?? $metodo;
            $totalsByMethod[$label] = $items->sum('monto');
        }

        $totalsByAccount = [];
        foreach ($recaudaciones->groupBy('cuenta_recaudadora_id') as $accountId => $items) {
            $account = $items->first()?->cuentaRecaudadora;
            $label = $account ? $account->banco . ' - ' . $account->numero_cuenta : __('Unknown');
            $totalsByAccount[$label] = $items->sum('monto');
        }

        return [$totalsByService, $totalsByMethod, $totalsByAccount];
    }

    private function buildConciliacionTotals($movimientos): array
    {
        $totals = [];
        foreach ($movimientos->groupBy('estado_conciliacion') as $estado => $items) {
            $totals[ucfirst($estado)] = $items->count();
        }

        return $totals;
    }

    protected function buildBudgetExecutionExportRows(array $rows, array $totals, string $budgetLabel, string $classifierLabel): array
    {
        $exportRows = [];
        $exportRows[] = [__('Budget Execution Report')];
        $exportRows[] = [__('Period'), $budgetLabel];
        $exportRows[] = [__('Classifier'), $classifierLabel];
        $exportRows[] = [''];
        $exportRows[] = [
            __('Classifier'),
            __('PIA'),
            __('PIM'),
            __('Commitment'),
            __('Accrued'),
            __('Paid'),
        ];

        foreach ($rows as $row) {
            $exportRows[] = [
                $row['label'],
                $row['pia'],
                $row['pim'],
                $row['compromiso'],
                $row['devengado'],
                $row['pagado'],
            ];
        }

        $exportRows[] = [
            __('Total'),
            $totals['pia'],
            $totals['pim'],
            $totals['compromiso'],
            $totals['devengado'],
            $totals['pagado'],
        ];

        return $exportRows;
    }

    protected function buildPublicBudgetExecutionExportRows(
        array $rows,
        array $totals,
        string $budgetLabel,
        string $classifierLabel,
        string $companyName
    ): array {
        $exportRows = [];
        $exportRows[] = [$companyName !== '' ? $companyName : __('Institutional Report')];
        $exportRows[] = [__('Public Budget Execution Report')];
        $exportRows[] = [__('Period'), $budgetLabel];
        $exportRows[] = [__('Classifier'), $classifierLabel];
        $exportRows[] = [__('Generated at'), now()->format('Y-m-d H:i')];
        $exportRows[] = [''];
        $exportRows[] = [
            __('Classifier'),
            __('PIA'),
            __('PIM'),
            __('Commitment'),
            __('Accrued'),
            __('Paid'),
            __('Execution Balance'),
        ];

        foreach ($rows as $row) {
            $exportRows[] = [
                $row['label'],
                $row['pia'],
                $row['pim'],
                $row['compromiso'],
                $row['devengado'],
                $row['pagado'],
                $row['saldo'],
            ];
        }

        $exportRows[] = [
            __('Total'),
            $totals['pia'],
            $totals['pim'],
            $totals['compromiso'],
            $totals['devengado'],
            $totals['pagado'],
            $totals['saldo'],
        ];

        return $exportRows;
    }

    public function productStock(Request $request)
    {
        if (\Auth::user()->can('stock report')) {
            $stocks = StockReport::where('created_by', '=', \Auth::user()->creatorId())->get();
            return view('report.product_stock_report', compact('stocks'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function export()
    {
        $name = 'account_statement' . date('Y-m-d i:h:s');
        $data = Excel::download(new AccountStatementExport(), $name . '.xlsx');

        return $data;
    }

    public function stock_export()
    {
        $name = 'Product_Stock' . date('Y-m-d i:h:s');
        $data = Excel::download(new ProductStockExport(), $name . '.xlsx');

        return $data;
    }

    public function dgii606(Request $request, Dgii606Service $service)
    {
        if (!\Auth::user()->can('manage bill')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $selectedYear = (int) ($request->get('year', date('Y')));
        $selectedMonth = (int) ($request->get('month', date('m')));
        $years = [];
        for ($year = date('Y') - 5; $year <= date('Y') + 1; $year++) {
            $years[$year] = $year;
        }
        $months = [
            1 => __('January'),
            2 => __('February'),
            3 => __('March'),
            4 => __('April'),
            5 => __('May'),
            6 => __('June'),
            7 => __('July'),
            8 => __('August'),
            9 => __('September'),
            10 => __('October'),
            11 => __('November'),
            12 => __('December'),
        ];

        if ($request->get('action') === 'download') {
            $fileName = sprintf('DGII_606_%04d_%02d.xlsx', $selectedYear, $selectedMonth);

            return Excel::download(
                new Dgii606Export($selectedYear, $selectedMonth, \Auth::user()->creatorId(), $service),
                $fileName
            );
        }

        return view('report.dgii606', compact('selectedMonth', 'selectedYear', 'months', 'years'));
    }

    public function dgii607(Request $request, Dgii607Service $service)
    {
        if (!\Auth::user()->can('reportes_dgii_607_generate')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $selectedYear = (int) ($request->get('year', date('Y')));
        $selectedMonth = (int) ($request->get('month', date('m')));
        $years = [];
        for ($year = date('Y') - 5; $year <= date('Y') + 1; $year++) {
            $years[$year] = $year;
        }
        $months = [
            1 => __('January'),
            2 => __('February'),
            3 => __('March'),
            4 => __('April'),
            5 => __('May'),
            6 => __('June'),
            7 => __('July'),
            8 => __('August'),
            9 => __('September'),
            10 => __('October'),
            11 => __('November'),
            12 => __('December'),
        ];

        if ($request->get('action') === 'download') {
            $fileName = sprintf('DGII_607_%04d_%02d.xlsx', $selectedYear, $selectedMonth);

            return Excel::download(
                new Dgii607Export($selectedYear, $selectedMonth, \Auth::user()->creatorId(), $service),
                $fileName
            );
        }

        return view('report.dgii607', compact('selectedMonth', 'selectedYear', 'months', 'years'));
    }

    public function dgii608(Request $request, Dgii608Service $service)
    {
        if (!\Auth::user()->can('reportes_dgii_608_generate')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $selectedYear = (int) ($request->get('year', date('Y')));
        $selectedMonth = (int) ($request->get('month', date('m')));
        $years = [];
        for ($year = date('Y') - 5; $year <= date('Y') + 1; $year++) {
            $years[$year] = $year;
        }
        $months = [
            1 => __('January'),
            2 => __('February'),
            3 => __('March'),
            4 => __('April'),
            5 => __('May'),
            6 => __('June'),
            7 => __('July'),
            8 => __('August'),
            9 => __('September'),
            10 => __('October'),
            11 => __('November'),
            12 => __('December'),
        ];

        if ($request->get('action') === 'download') {
            $fileName = sprintf('DGII_608_%04d_%02d.xlsx', $selectedYear, $selectedMonth);

            return Excel::download(
                new Dgii608Export($selectedYear, $selectedMonth, \Auth::user()->creatorId(), $service),
                $fileName
            );
        }

        return view('report.dgii608', compact('selectedMonth', 'selectedYear', 'months', 'years'));
    }

    public function payrollCostsByService(Request $request, NominaAsientoService $asientoService)
    {
        if (!\Auth::user()->can('nomina_periodos_manage') && !\Auth::user()->can('ledger report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $periodos = NominaPeriodo::where('created_by', $creatorId)->get();
        $periodoSeleccionado = null;
        $resumen = [
            'services' => [],
            'totales' => [
                'gastos' => 0,
                'descuentos' => 0,
                'neto' => 0,
            ],
        ];

        if ($request->filled('nomina_periodo_id')) {
            $periodoSeleccionado = $periodos->firstWhere('id', $request->nomina_periodo_id);
            if ($periodoSeleccionado) {
                $resumen = $asientoService->previewAsientoPorServicio($periodoSeleccionado, $creatorId);
            }
        }

        return view('report.payroll_costs_by_service', compact('periodos', 'periodoSeleccionado', 'resumen'));
    }

    public function payrollCostsByServiceExport(Request $request, NominaAsientoService $asientoService)
    {
        if (!\Auth::user()->can('nomina_periodos_manage') && !\Auth::user()->can('ledger report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'nomina_periodo_id' => 'required|integer',
        ]);

        $creatorId = \Auth::user()->creatorId();
        $periodo = NominaPeriodo::where('created_by', $creatorId)->find($request->nomina_periodo_id);
        if (!$periodo) {
            return redirect()->back()->with('error', __('Payroll period not found.'));
        }

        $resumen = $asientoService->previewAsientoPorServicio($periodo, $creatorId);

        return Excel::download(
            new NominaCostosServicioExport($resumen['services'], $resumen['totales'], $periodo),
            'costos-nomina-servicio.xlsx'
        );
    }

    public function payrollCostsByServicePdf(Request $request, NominaAsientoService $asientoService)
    {
        if (!\Auth::user()->can('nomina_periodos_manage') && !\Auth::user()->can('ledger report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'nomina_periodo_id' => 'required|integer',
        ]);

        $creatorId = \Auth::user()->creatorId();
        $periodo = NominaPeriodo::where('created_by', $creatorId)->find($request->nomina_periodo_id);
        if (!$periodo) {
            return redirect()->back()->with('error', __('Payroll period not found.'));
        }

        $resumen = $asientoService->previewAsientoPorServicio($periodo, $creatorId);
        $settings = Utility::settings();

        $pdf = Pdf::loadView('report.payroll_costs_by_service_pdf', [
            'periodo' => $periodo,
            'resumen' => $resumen,
            'settings' => $settings,
        ]);

        return $pdf->download('costos-nomina-servicio.pdf');
    }
}
