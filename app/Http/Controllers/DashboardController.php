<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Revenue;
use App\Models\Tax;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        // Si no está autenticado, mantiene tu lógica actual
        if (!Auth::check()) {
            if (!file_exists(storage_path() . "/installed")) {
                header('location:install');
                die;
            }

            $settings = Utility::settings();

            if (($settings['display_landing_page'] ?? null) === 'on' && \Schema::hasTable('landing_page_settings')) {
                return view('landingpage::layouts.landingpage');
            }

            return redirect('login');
        }

        // Si está autenticado pero NO tiene permiso, NO renderizamos el dashboard (evita undefined vars)
        if (!\Auth::user()->can('show dashboard')) {
            abort(403, 'No autorizado para ver el dashboard.');
        }

        $data = [];

        // Últimos registros
        $data['latestIncome']  = Revenue::where('created_by', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
        $data['latestExpense'] = Payment::where('created_by', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();

        // Income by category
        $incomeCategory = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'income')
            ->get();

        $inColor    = [];
        $inCategory = [];
        $inAmount   = [];

        foreach ($incomeCategory as $cat) {
            $inColor[]    = $cat->color;
            $inCategory[] = $cat->name;
            $inAmount[]   = $cat->incomeCategoryRevenueAmount();
        }

        $data['incomeCategoryColor'] = $inColor;
        $data['incomeCategory']      = $inCategory;
        $data['incomeCatAmount']     = $inAmount;

        // Expense by category
        $expenseCategory = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'expense')
            ->get();

        $exColor    = [];
        $exCategory = [];
        $exAmount   = [];

        foreach ($expenseCategory as $cat) {
            $exColor[]    = $cat->color;
            $exCategory[] = $cat->name;
            $exAmount[]   = $cat->expenseCategoryAmount();
        }

        $data['expenseCategoryColor'] = $exColor;
        $data['expenseCategory']      = $exCategory;
        $data['expenseCatAmount']     = $exAmount;

        // Charts
        $data['incExpBarChartData'] = \Auth::user()->getincExpBarChartData();

        // Algunos proyectos tienen el método mal nombrado (Date vs Data). Probamos ambos.
        if (method_exists(\Auth::user(), 'getIncExpLineChartData')) {
            $data['incExpLineChartData'] = \Auth::user()->getIncExpLineChartData();
        } elseif (method_exists(\Auth::user(), 'getIncExpLineChartDate')) {
            $data['incExpLineChartData'] = \Auth::user()->getIncExpLineChartDate();
        } else {
            // Default seguro para que el blade nunca truene
            $data['incExpLineChartData'] = ['income' => [], 'expense' => [], 'day' => []];
        }

        $data['currentYear']  = date('Y');
        $data['currentMonth'] = date('M');

        // Constantes
        $constant = [];
        $constant['taxes']       = Tax::where('created_by', \Auth::user()->creatorId())->count();
        $constant['category']    = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->count();
        $constant['units']       = ProductServiceUnit::where('created_by', \Auth::user()->creatorId())->count();
        $constant['bankAccount'] = BankAccount::where('created_by', \Auth::user()->creatorId())->count();
        $data['constant']        = $constant;

        // Otros widgets/tablas
        $data['bankAccountDetail'] = BankAccount::where('created_by', \Auth::user()->creatorId())->get();
        $data['recentInvoice']     = Invoice::where('created_by', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
        $data['weeklyInvoice']     = \Auth::user()->weeklyInvoice();
        $data['monthlyInvoice']    = \Auth::user()->monthlyInvoice();
        $data['recentBill']        = Bill::where('created_by', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
        $data['weeklyBill']        = \Auth::user()->weeklyBill();
        $data['monthlyBill']       = \Auth::user()->monthlyBill();
        $data['goals']             = Goal::where('created_by', \Auth::user()->creatorId())->where('is_display', 1)->get();

        return view('dashboard.index', $data);
    }
}