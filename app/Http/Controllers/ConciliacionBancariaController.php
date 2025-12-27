<?php

namespace App\Http\Controllers;

use App\Models\CuentaRecaudadora;
use App\Models\MovimientoBancario;
use App\Models\Payment;
use App\Models\Recaudacion;
use App\Services\ConciliacionBancariaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConciliacionBancariaController extends Controller
{
    public function index(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_conciliacion_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $cuentasQuery = CuentaRecaudadora::query()->where('activo', true);
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $cuentasQuery->where('created_by', $creatorId);
        }
        $cuentas = $cuentasQuery->orderBy('banco')->orderBy('numero_cuenta')->get();

        $movimientosQuery = MovimientoBancario::with(['cuentaRecaudadora', 'conciliable'])->orderBy('fecha', 'desc');
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $movimientosQuery->whereIn('cuenta_recaudadora_id', $cuentas->pluck('id'));
        }

        if ($request->filled('estado')) {
            $movimientosQuery->where('estado_conciliacion', $request->estado);
        }

        if ($request->filled('cuenta_recaudadora_id')) {
            $movimientosQuery->where('cuenta_recaudadora_id', $request->cuenta_recaudadora_id);
        }

        $movimientos = $movimientosQuery->get();
        $cuentaOptions = $cuentas->mapWithKeys(function ($cuenta) {
            return [$cuenta->id => $cuenta->banco . ' - ' . $cuenta->numero_cuenta];
        });

        $recaudacionesQuery = Recaudacion::query()
            ->where('estado_conciliacion', 'pendiente')
            ->orderBy('fecha', 'desc');
        $pagosQuery = Payment::query()
            ->where('estado_conciliacion', 'pendiente')
            ->orderBy('date', 'desc');

        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $recaudacionesQuery->where('created_by', $creatorId);
            $pagosQuery->where('created_by', $creatorId);
        }

        $recaudaciones = $recaudacionesQuery->limit(200)->get();
        $pagos = $pagosQuery->limit(200)->get();

        return view('tesoreria.conciliacion.index', compact('cuentas', 'movimientos', 'recaudaciones', 'pagos', 'cuentaOptions'));
    }

    public function conciliarAutomaticamente(Request $request, ConciliacionBancariaService $service)
    {
        if (!\Auth::user()->can('tesoreria_conciliacion_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $movimientosQuery = MovimientoBancario::query()->where('estado_conciliacion', 'pendiente');
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $cuentaIds = CuentaRecaudadora::query()
                ->where('activo', true)
                ->where('created_by', \Auth::user()->creatorId())
                ->pluck('id');
            $movimientosQuery->whereIn('cuenta_recaudadora_id', $cuentaIds);
        }

        if ($request->filled('cuenta_recaudadora_id')) {
            $movimientosQuery->where('cuenta_recaudadora_id', $request->cuenta_recaudadora_id);
        }

        $movimientos = $movimientosQuery->get();

        $resultado = $service->conciliarAutomaticamente($movimientos);

        return redirect()
            ->route('tesoreria.conciliacion.index')
            ->with('success', __('Automatic reconciliation completed.') . ' ' . __('Matched') . ': ' . $resultado['conciliados'] . ', ' . __('Partial') . ': ' . $resultado['parciales'] . '.');
    }

    public function conciliarManual(Request $request, ConciliacionBancariaService $service)
    {
        if (!\Auth::user()->can('tesoreria_conciliacion_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'movimiento_id' => ['required', 'integer', 'exists:movimientos_bancarios,id'],
                'tipo' => ['required', Rule::in(['recaudacion', 'pago'])],
                'registro_id' => ['required', 'integer'],
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $movimiento = MovimientoBancario::findOrFail($request->movimiento_id);
        if ($movimiento->estado_conciliacion !== 'pendiente') {
            return redirect()->back()->with('error', __('The bank movement is already reconciled.'));
        }

        if ($request->tipo === 'recaudacion') {
            $registro = Recaudacion::where('estado_conciliacion', 'pendiente')
                ->findOrFail($request->registro_id);
        } else {
            $registro = Payment::where('estado_conciliacion', 'pendiente')
                ->findOrFail($request->registro_id);
        }

        $service->conciliarMovimiento($movimiento, $registro);

        return redirect()->back()->with('success', __('Manual reconciliation applied successfully.'));
    }
}
