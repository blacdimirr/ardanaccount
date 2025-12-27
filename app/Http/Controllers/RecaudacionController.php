<?php

namespace App\Http\Controllers;

use App\Models\CuentaRecaudadora;
use App\Models\Recaudacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class RecaudacionController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $recaudacionesQuery = Recaudacion::with('cuentaRecaudadora');
        $cuentasQuery = CuentaRecaudadora::query()->where('activo', true);

        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $recaudacionesQuery->where('created_by', $creatorId);
            $cuentasQuery->where('created_by', $creatorId);
        }

        $recaudaciones = $recaudacionesQuery->orderBy('fecha', 'desc')->get();
        $cuentas = $cuentasQuery->orderBy('banco')->orderBy('numero_cuenta')->get();

        $servicios = $this->servicioOptions();
        $metodosPago = $this->metodoPagoOptions();

        return view('tesoreria.recaudaciones.index', compact('recaudaciones', 'cuentas', 'servicios', 'metodosPago'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'fecha' => 'required|date',
                'servicio' => 'required|in:consulta,copago,privado',
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|string|max:191',
                'cuenta_recaudadora_id' => [
                    'required',
                    'integer',
                    'min:1',
                    $this->cuentaRecaudadoraRule(),
                ],
                'paciente_id' => 'nullable|integer|min:1',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('tesoreria.recaudaciones.index')->with('error', $messages->first());
        }

        $recaudacion = new Recaudacion();
        $recaudacion->fecha = $request->fecha;
        $recaudacion->servicio = $request->servicio;
        $recaudacion->monto = $request->monto;
        $recaudacion->metodo_pago = $request->metodo_pago;
        $recaudacion->cuenta_recaudadora_id = $request->cuenta_recaudadora_id;
        $recaudacion->paciente_id = $request->paciente_id;
        $recaudacion->created_by = \Auth::user()->creatorId();
        $recaudacion->save();

        return redirect()->route('tesoreria.recaudaciones.index')->with('success', __('Collection successfully recorded.'));
    }

    public function edit(Recaudacion $recaudacion)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (!$this->canAccessRecaudacion($recaudacion)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $cuentasQuery = CuentaRecaudadora::query()->where('activo', true);
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $cuentasQuery->where('created_by', \Auth::user()->creatorId());
        }
        $cuentas = $cuentasQuery->orderBy('banco')->orderBy('numero_cuenta')->get();

        $servicios = $this->servicioOptions();
        $metodosPago = $this->metodoPagoOptions();

        return view('tesoreria.recaudaciones.edit', compact('recaudacion', 'cuentas', 'servicios', 'metodosPago'));
    }

    public function update(Request $request, Recaudacion $recaudacion)
    {
        if (!\Auth::user()->can('tesoreria_recaudaciones_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!$this->canAccessRecaudacion($recaudacion)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'fecha' => 'required|date',
                'servicio' => 'required|in:consulta,copago,privado',
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|string|max:191',
                'cuenta_recaudadora_id' => [
                    'required',
                    'integer',
                    'min:1',
                    $this->cuentaRecaudadoraRule(),
                ],
                'paciente_id' => 'nullable|integer|min:1',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('tesoreria.recaudaciones.index')->with('error', $messages->first());
        }

        $recaudacion->fecha = $request->fecha;
        $recaudacion->servicio = $request->servicio;
        $recaudacion->monto = $request->monto;
        $recaudacion->metodo_pago = $request->metodo_pago;
        $recaudacion->cuenta_recaudadora_id = $request->cuenta_recaudadora_id;
        $recaudacion->paciente_id = $request->paciente_id;
        if ($recaudacion->created_by != \Auth::user()->creatorId() && \Auth::user()->type === 'super admin') {
            $recaudacion->created_by = $recaudacion->created_by;
        } else {
            $recaudacion->created_by = \Auth::user()->creatorId();
        }
        $recaudacion->save();

        return redirect()->route('tesoreria.recaudaciones.index')->with('success', __('Collection successfully updated.'));
    }

    private function servicioOptions(): array
    {
        return [
            'consulta' => __('Consultation'),
            'copago' => __('Copay'),
            'privado' => __('Private Service'),
        ];
    }

    private function metodoPagoOptions(): array
    {
        return [
            'efectivo' => __('Cash'),
            'tarjeta' => __('Card'),
            'transferencia' => __('Transfer'),
            'cheque' => __('Check'),
            'otro' => __('Other'),
        ];
    }

    private function canAccessRecaudacion(Recaudacion $recaudacion): bool
    {
        return $recaudacion->created_by == \Auth::user()->creatorId() || \Auth::user()->type === 'super admin';
    }

    private function cuentaRecaudadoraRule(): Exists
    {
        $rule = Rule::exists('cuentas_recaudadoras', 'id')->where('activo', true);

        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $rule->where('created_by', \Auth::user()->creatorId());
        }

        return $rule;
    }
}
