<?php

namespace App\Http\Controllers;

use App\Models\CuentaRecaudadora;
use Illuminate\Http\Request;

class CuentaRecaudadoraController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cuentasQuery = CuentaRecaudadora::query();
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $cuentasQuery->where('created_by', \Auth::user()->creatorId());
        }

        $cuentas = $cuentasQuery->orderBy('banco')->orderBy('numero_cuenta')->get();

        return view('tesoreria.cuentas_recaudadoras.index', compact('cuentas'));
    }

    public function create()
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('tesoreria.cuentas_recaudadoras.create');
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'banco' => 'required|string|max:191',
                'numero_cuenta' => 'required|string|max:191',
                'tipo' => 'required|string|max:191',
                'activo' => 'nullable|boolean',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('tesoreria.cuentas-recaudadoras.index')->with('error', $messages->first());
        }

        $cuenta = new CuentaRecaudadora();
        $cuenta->banco = $request->banco;
        $cuenta->numero_cuenta = $request->numero_cuenta;
        $cuenta->tipo = $request->tipo;
        $cuenta->activo = $request->boolean('activo', true);
        $cuenta->created_by = \Auth::user()->creatorId();
        $cuenta->save();

        return redirect()->route('tesoreria.cuentas-recaudadoras.index')->with('success', __('Collection account successfully created.'));
    }

    public function edit(CuentaRecaudadora $cuentas_recaudadora)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (!$this->canAccessCuenta($cuentas_recaudadora)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('tesoreria.cuentas_recaudadoras.edit', ['cuenta' => $cuentas_recaudadora]);
    }

    public function update(Request $request, CuentaRecaudadora $cuentas_recaudadora)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!$this->canAccessCuenta($cuentas_recaudadora)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'banco' => 'required|string|max:191',
                'numero_cuenta' => 'required|string|max:191',
                'tipo' => 'required|string|max:191',
                'activo' => 'nullable|boolean',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('tesoreria.cuentas-recaudadoras.index')->with('error', $messages->first());
        }

        $cuentas_recaudadora->banco = $request->banco;
        $cuentas_recaudadora->numero_cuenta = $request->numero_cuenta;
        $cuentas_recaudadora->tipo = $request->tipo;
        $cuentas_recaudadora->activo = $request->boolean('activo', true);
        if ($cuentas_recaudadora->created_by != \Auth::user()->creatorId() && \Auth::user()->type === 'super admin') {
            $cuentas_recaudadora->created_by = $cuentas_recaudadora->created_by;
        } else {
            $cuentas_recaudadora->created_by = \Auth::user()->creatorId();
        }
        $cuentas_recaudadora->save();

        return redirect()->route('tesoreria.cuentas-recaudadoras.index')->with('success', __('Collection account successfully updated.'));
    }

    private function canAccessCuenta(CuentaRecaudadora $cuenta): bool
    {
        return $cuenta->created_by == \Auth::user()->creatorId() || \Auth::user()->type === 'super admin';
    }
}
