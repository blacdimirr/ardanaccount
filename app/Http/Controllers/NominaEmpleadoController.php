<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\ServicioUnidad;
use Illuminate\Http\Request;

class NominaEmpleadoController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleados = Empleado::with('servicioUnidad')
            ->where('created_by', \Auth::user()->creatorId())
            ->get();

        return view('nomina.empleados.index', compact('empleados'));
    }

    public function create()
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $tiposVinculo = $this->tiposVinculo();
        $tiposContribuyente = $this->tiposContribuyente();
        $servicios = ServicioUnidad::where('created_by', \Auth::user()->creatorId())
            ->orderBy('nombre')
            ->get();

        return view('nomina.empleados.create', compact('tiposVinculo', 'tiposContribuyente', 'servicios'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'tipo_vinculo' => 'required',
                'tipo_contribuyente' => 'required|in:asalariado,honorarios',
                'servicio_id' => 'nullable|integer|exists:servicios_unidades,id',
                'unidad_servicio' => 'required_without:servicio_id',
                'salario' => 'required|numeric|min:0',
                'email' => 'nullable|email',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->filled('servicio_id')) {
            $servicioValido = ServicioUnidad::where('created_by', \Auth::user()->creatorId())
                ->where('id', $request->servicio_id)
                ->exists();

            if (!$servicioValido) {
                return redirect()->back()->with('error', __('Servicio seleccionado inválido.'));
            }
        }

        $empleado = new Empleado();
        $empleado->first_name = $request->first_name;
        $empleado->last_name = $request->last_name;
        $empleado->documento_identidad = $request->documento_identidad;
        $empleado->email = $request->email;
        $empleado->telefono = $request->telefono;
        $empleado->direccion = $request->direccion;
        $empleado->tipo_vinculo = $request->tipo_vinculo;
        $empleado->tipo_contribuyente = $request->tipo_contribuyente;
        $empleado->servicio_id = $request->servicio_id;
        $empleado->unidad_servicio = $request->unidad_servicio;
        $empleado->salario = $request->salario;
        $empleado->created_by = \Auth::user()->creatorId();

        if ($empleado->servicio_id && !$empleado->unidad_servicio) {
            $empleado->unidad_servicio = ServicioUnidad::where('created_by', \Auth::user()->creatorId())
                ->where('id', $empleado->servicio_id)
                ->value('nombre');
        }

        $empleado->save();

        return redirect()->route('nomina-empleados.index')->with('success', __('Employee successfully created.'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $empleado = Empleado::find($id);
        if ($empleado->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $tiposVinculo = $this->tiposVinculo();
        $tiposContribuyente = $this->tiposContribuyente();
        $servicios = ServicioUnidad::where('created_by', \Auth::user()->creatorId())
            ->orderBy('nombre')
            ->get();

        return view('nomina.empleados.edit', compact('empleado', 'tiposVinculo', 'tiposContribuyente', 'servicios'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleado = Empleado::find($id);
        if ($empleado->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'tipo_vinculo' => 'required',
                'tipo_contribuyente' => 'required|in:asalariado,honorarios',
                'servicio_id' => 'nullable|integer|exists:servicios_unidades,id',
                'unidad_servicio' => 'required_without:servicio_id',
                'salario' => 'required|numeric|min:0',
                'email' => 'nullable|email',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->filled('servicio_id')) {
            $servicioValido = ServicioUnidad::where('created_by', \Auth::user()->creatorId())
                ->where('id', $request->servicio_id)
                ->exists();

            if (!$servicioValido) {
                return redirect()->back()->with('error', __('Servicio seleccionado inválido.'));
            }
        }

        $empleado->first_name = $request->first_name;
        $empleado->last_name = $request->last_name;
        $empleado->documento_identidad = $request->documento_identidad;
        $empleado->email = $request->email;
        $empleado->telefono = $request->telefono;
        $empleado->direccion = $request->direccion;
        $empleado->tipo_vinculo = $request->tipo_vinculo;
        $empleado->tipo_contribuyente = $request->tipo_contribuyente;
        $empleado->servicio_id = $request->servicio_id;
        $empleado->unidad_servicio = $request->unidad_servicio;
        $empleado->salario = $request->salario;

        if ($empleado->servicio_id && !$empleado->unidad_servicio) {
            $empleado->unidad_servicio = ServicioUnidad::where('created_by', \Auth::user()->creatorId())
                ->where('id', $empleado->servicio_id)
                ->value('nombre');
        }

        $empleado->save();

        return redirect()->route('nomina-empleados.index')->with('success', __('Employee successfully updated.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleado = Empleado::find($id);
        if ($empleado->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleado->delete();

        return redirect()->route('nomina-empleados.index')->with('success', __('Employee successfully deleted.'));
    }

    private function tiposVinculo()
    {
        return [
            'nombrado' => __('Nombrado'),
            'contrato' => __('Contrato'),
            'honorarios' => __('Honorarios'),
        ];
    }

    private function tiposContribuyente()
    {
        return [
            'asalariado' => __('Asalariado'),
            'honorarios' => __('Honorarios'),
        ];
    }
}
