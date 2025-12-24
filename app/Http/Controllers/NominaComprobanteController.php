<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\NominaDetalle;
use App\Models\NominaPeriodo;
use Illuminate\Http\Request;

class NominaComprobanteController extends Controller
{
    public function index(Request $request)
    {
        if (!\Auth::user()->can('nomina_periodos_manage') && !\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleados = Empleado::where('created_by', \Auth::user()->creatorId())->get();
        $periodos = NominaPeriodo::where('created_by', \Auth::user()->creatorId())->get();

        $periodoSeleccionado = null;
        $empleadoSeleccionado = null;
        $detalles = collect();
        $detallesVisibles = collect();
        $total = 0;

        if ($request->filled('nomina_periodo_id') && $request->filled('empleado_id')) {
            $periodoSeleccionado = $periodos->firstWhere('id', $request->nomina_periodo_id);
            $empleadoSeleccionado = $empleados->firstWhere('id', $request->empleado_id);

            if ($periodoSeleccionado && $empleadoSeleccionado) {
                $detalles = NominaDetalle::with('concepto')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('nomina_periodo_id', $periodoSeleccionado->id)
                    ->where('empleado_id', $empleadoSeleccionado->id)
                    ->get();

                $detallesVisibles = $detalles->filter(function ($detalle) {
                    return !$detalle->concepto || $detalle->concepto->tipo !== 'aporte';
                });
                $total = $detallesVisibles
                    ->filter(function ($detalle) {
                        return $detalle->concepto && $detalle->concepto->tipo !== 'aporte';
                    })
                    ->sum('monto');
            }
        }

        return view(
            'nomina.comprobantes.index',
            compact('empleados', 'periodos', 'periodoSeleccionado', 'empleadoSeleccionado', 'detalles', 'detallesVisibles', 'total')
        );
    }
}
