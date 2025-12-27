<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\FondoRotatorio;
use App\Models\MovimientoFondo;
use Illuminate\Http\Request;

class FondoRotatorioController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $fondosQuery = FondoRotatorio::with('cuentaContable');
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $fondosQuery->where('created_by', \Auth::user()->creatorId());
        }

        $fondos = $fondosQuery->orderBy('nombre')->get();

        return view('tesoreria.fondos.index', compact('fondos'));
    }

    public function create()
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        [$chartAccounts, $subAccounts] = $this->getChartAccounts();

        return view('tesoreria.fondos.create', compact('chartAccounts', 'subAccounts'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'nombre' => 'required|string|max:191',
                'monto_inicial' => 'required|numeric|min:0',
                'cuenta_contable_id' => 'required|integer|min:1',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('tesoreria.fondos.index')->with('error', $messages->first());
        }

        $fondo = new FondoRotatorio();
        $fondo->nombre = $request->nombre;
        $fondo->monto_inicial = $request->monto_inicial;
        $fondo->monto_disponible = $request->monto_inicial;
        $fondo->cuenta_contable_id = $this->resolveChartAccountForTenant((int) $request->cuenta_contable_id);
        $fondo->created_by = \Auth::user()->creatorId();
        $fondo->save();

        return redirect()->route('tesoreria.fondos.index')->with('success', __('Fund successfully created.'));
    }

    public function edit(FondoRotatorio $fondo)
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (!$this->canAccessFondo($fondo)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        [$chartAccounts, $subAccounts] = $this->getChartAccounts();

        return view('tesoreria.fondos.edit', compact('fondo', 'chartAccounts', 'subAccounts'));
    }

    public function update(Request $request, FondoRotatorio $fondo)
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!$this->canAccessFondo($fondo)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'nombre' => 'required|string|max:191',
                'monto_inicial' => 'required|numeric|min:0',
                'cuenta_contable_id' => 'required|integer|min:1',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('tesoreria.fondos.index')->with('error', $messages->first());
        }

        $fondo->nombre = $request->nombre;
        $fondo->monto_inicial = $request->monto_inicial;
        $fondo->cuenta_contable_id = $this->resolveChartAccountForTenant((int) $request->cuenta_contable_id);
        if ($fondo->created_by != \Auth::user()->creatorId() && \Auth::user()->type === 'super admin') {
            $fondo->created_by = $fondo->created_by;
        } else {
            $fondo->created_by = \Auth::user()->creatorId();
        }
        $fondo->monto_disponible = $this->calculateDisponible($fondo->id, (float) $request->monto_inicial);
        $fondo->save();

        return redirect()->route('tesoreria.fondos.index')->with('success', __('Fund successfully updated.'));
    }

    public function createMovimiento(FondoRotatorio $fondo)
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (!$this->canAccessFondo($fondo)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('tesoreria.fondos.movimiento', compact('fondo'));
    }

    public function storeMovimiento(Request $request, FondoRotatorio $fondo)
    {
        if (!\Auth::user()->can('tesoreria_fondos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!$this->canAccessFondo($fondo)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'tipo' => 'required|in:egreso,reposicion',
                'monto' => 'required|numeric|min:0.01',
                'fecha' => 'required|date',
                'descripcion' => 'nullable|string',
                'comprobante_id' => 'nullable|integer',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('tesoreria.fondos.index')->with('error', $messages->first());
        }

        $monto = (float) $request->monto;
        if ($request->tipo === 'egreso' && $monto > $fondo->monto_disponible) {
            return redirect()->route('tesoreria.fondos.index')->with('error', __('Insufficient fund balance for this expense.'));
        }

        $movimiento = new MovimientoFondo();
        $movimiento->fondo_id = $fondo->id;
        $movimiento->tipo = $request->tipo;
        $movimiento->monto = $monto;
        $movimiento->fecha = $request->fecha;
        $movimiento->descripcion = $request->descripcion;
        $movimiento->comprobante_id = $request->comprobante_id;
        $movimiento->created_by = $fondo->created_by;
        $movimiento->save();

        if ($request->tipo === 'egreso') {
            $fondo->monto_disponible = $fondo->monto_disponible - $monto;
        } else {
            $fondo->monto_disponible = $fondo->monto_disponible + $monto;
        }
        $fondo->save();

        return redirect()->route('tesoreria.fondos.index')->with('success', __('Fund movement successfully recorded.'));
    }

    private function getChartAccounts(): array
    {
        $chartAccountsQuery = ChartOfAccount::select([\DB::raw('CONCAT(code, " - ", name) AS code_name'), 'id'])
            ->where('parent', '=', 0);
        if (\Auth::user()->type !== 'super admin') {
            $chartAccountsQuery->where('created_by', \Auth::user()->creatorId());
        } else {
            $chartAccountsQuery->whereIn('created_by', [\Auth::user()->creatorId(), 1]);
        }
        $chartAccounts = $chartAccountsQuery->get()
            ->pluck('code_name', 'id')
            ->prepend('Select Account', 0);

        $subAccountsQuery = ChartOfAccount::select(['chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account'])
            ->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
            ->where('chart_of_accounts.parent', '!=', 0);
        if (\Auth::user()->type !== 'super admin') {
            $subAccountsQuery->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
        } else {
            $subAccountsQuery->whereIn('chart_of_accounts.created_by', [\Auth::user()->creatorId(), 1]);
        }
        $subAccounts = $subAccountsQuery->get()->toArray();

        return [$chartAccounts, $subAccounts];
    }

    private function calculateDisponible(int $fondoId, float $montoInicial): float
    {
        $totals = MovimientoFondo::selectRaw("sum(case when tipo = 'reposicion' then monto else 0 end) as reposiciones")
            ->selectRaw("sum(case when tipo = 'egreso' then monto else 0 end) as egresos")
            ->where('fondo_id', $fondoId)
            ->first();

        $reposiciones = $totals?->reposiciones ?? 0;
        $egresos = $totals?->egresos ?? 0;

        return $montoInicial + $reposiciones - $egresos;
    }

    private function resolveChartAccountForTenant(int $accountId): int
    {
        $account = ChartOfAccount::find($accountId);
        if (!$account) {
            return $accountId;
        }

        $creatorId = \Auth::user()->creatorId();
        if ((int) $account->created_by === $creatorId) {
            return $account->id;
        }

        $existing = ChartOfAccount::where('created_by', $creatorId)
            ->where('code', $account->code)
            ->first();

        return $existing ? $existing->id : $account->id;
    }

    private function canAccessFondo(FondoRotatorio $fondo): bool
    {
        return $fondo->created_by == \Auth::user()->creatorId() || \Auth::user()->type === 'super admin';
    }
}
